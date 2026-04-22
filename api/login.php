<?php
/**
 * api/login.php — Sikker server-side login
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Start sikker session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
session_name(SESSION_NAME);
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metode ikke tilladt']);
    exit;
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);
$pw   = $body['password'] ?? '';

// Lille forsinkelse for at gøre brute-force sværere
usleep(300000); // 0.3 sekunder

if (!$pw || !password_verify($pw, ADMIN_PASSWORD_HASH)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Forkert password']);
    exit;
}

// Login lykkedes — opret session
$_SESSION['authenticated'] = true;
$_SESSION['login_time']    = time();
$_SESSION['expires_at']    = time() + SESSION_TIMEOUT;

// Regenerer session-ID for at forhindre session fixation
session_regenerate_id(true);

echo json_encode(['ok' => true]);
