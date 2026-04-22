<?php
/**
 * api/contact.php — Kontaktformular med rate limiting
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Lås CORS til eget domæne
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = ['https://zpolonius.dk', 'https://www.zpolonius.dk', 'https://test.zpolonius.dk'];
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Tillad samme-domæne requests uden origin header
    if (!empty($origin)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Ikke tilladt']);
        exit;
    }
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Kun POST tilladt']); exit;
}

// --- RATE LIMITING ---
// Max 5 beskeder per IP per time
$ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ip_hash  = md5($ip); // Hash IP for privatliv
$rate_dir = sys_get_temp_dir() . '/zp_rate/';
if (!is_dir($rate_dir)) mkdir($rate_dir, 0700, true);

$rate_file = $rate_dir . $ip_hash . '.json';
$now       = time();
$window    = 3600; // 1 time
$max_reqs  = 5;

$attempts = [];
if (file_exists($rate_file)) {
    $attempts = json_decode(file_get_contents($rate_file), true) ?? [];
}

// Fjern gamle forsøg
$attempts = array_filter($attempts, fn($t) => $t > $now - $window);

if (count($attempts) >= $max_reqs) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'For mange beskeder. Vent en time eller kontakt mig direkte på zacharias@polonius.dk']);
    exit;
}

// Registrer dette forsøg
$attempts[] = $now;
file_put_contents($rate_file, json_encode(array_values($attempts)));

// --- VALIDERING ---
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['name']) || empty($data['email']) || empty($data['message'])) {
    echo json_encode(['ok' => false, 'error' => 'Udfyld venligst alle felter']); exit;
}

$name    = htmlspecialchars(trim($data['name']));
$email   = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$company = htmlspecialchars(trim($data['company'] ?? ''));
$subject = htmlspecialchars(trim($data['subject'] ?? 'Generel henvendelse'));
$message = htmlspecialchars(trim($data['message']));

// Længdebegrænsning
if (strlen($name) > 100 || strlen($message) > 5000 || strlen($company) > 100) {
    echo json_encode(['ok' => false, 'error' => 'Teksten er for lang']); exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'Ugyldig email-adresse']); exit;
}

// --- SEND EMAIL ---
$emailSubject = "[$subject] Ny besked fra $name — zpolonius.dk";

$body  = "Ny besked fra zpolonius.dk\n";
$body .= str_repeat('-', 40) . "\n\n";
$body .= "Navn:    $name\n";
$body .= "Email:   $email\n";
if ($company) $body .= "Firma:   $company\n";
$body .= "Emne:    $subject\n\n";
$body .= "Besked:\n$message\n\n";
$body .= str_repeat('-', 40) . "\n";
$body .= "IP-hash: $ip_hash\n";
$body .= "Tidspunkt: " . date('Y-m-d H:i:s') . "\n";

$headers  = "From: noreply@zpolonius.dk\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

$sent = mail('zacharias@polonius.dk', $emailSubject, $body, $headers);

echo json_encode(['ok' => $sent ?: true]); // Vis succes selv ved mail-fejl for UX
