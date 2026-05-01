<?php
/**
 * api/login.php — Sikker server-side login
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Start sikker session
ini_set('session.cookie_httponly', 1);
// Kun secure cookie hvis vi kører over HTTPS
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
ini_set('session.cookie_secure', $is_https ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');
session_name(SESSION_NAME);
session_start();

// --- RATE LIMITING (Beskyttelse mod brute-force) ---
$ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ip_hash  = md5($ip);
$rate_dir = sys_get_temp_dir() . '/zp_auth_rate/';
if (!is_dir($rate_dir)) mkdir($rate_dir, 0700, true);

$rate_file = $rate_dir . $ip_hash . '.json';
$now       = time();
$window    = 900; // 15 minutter
$max_reqs  = 10;

$attempts = [];
if (file_exists($rate_file)) {
    $attempts = json_decode(file_get_contents($rate_file), true) ?? [];
}
$attempts = array_filter($attempts, fn($t) => $t > $now - $window);

if (count($attempts) >= $max_reqs) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'For mange login-forsøg. Vent 15 minutter.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metode ikke tilladt']);
    exit;
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);
$pw   = $body['password'] ?? '';

if (!$pw || !password_verify($pw, ADMIN_PASSWORD_HASH)) {
    // Registrer fejlet forsøg
    $attempts[] = $now;
    file_put_contents($rate_file, json_encode(array_values($attempts)));
    
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Forkert password']);
    exit;
}

// Login lykkedes — opret session
$_SESSION['authenticated'] = true;
$_SESSION['login_time']    = time();
$_SESSION['expires_at']    = time() + SESSION_TIMEOUT;

// Ryd rate limit ved succesfuld login
if (file_exists($rate_file)) unlink($rate_file);

// Regenerer session-ID for at forhindre session fixation
session_regenerate_id(true);

echo json_encode(['ok' => true]);
