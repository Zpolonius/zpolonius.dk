<?php
/**
 * api/track.php — Registrerer sidevisninger og unikke besøgende (privatlivsvenlig)
 */
header('Content-Type: application/json');

// Definer sti til analytics filen
$analyticsFile = __DIR__ . '/../data/analytics.json';

// Hent input data
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
$page = $input['page'] ?? 'unknown';

// Rens page string for sikkerhed
$page = filter_var($page, FILTER_SANITIZE_URL);

// Hent besøgendes data til hashing (anonymisering)
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$date = date('Y-m-d');

// Skab en anonym hash for dagen (IP + UA + Salt + Dato)
// Vi gemmer aldrig den rå IP.
$visitorHash = hash('sha256', $ip . $ua . 'zp_salt_2026' . $date);

// Hent eksisterende data eller start forfra
if (file_exists($analyticsFile)) {
    $data = json_decode(file_get_contents($analyticsFile), true);
} else {
    $data = [];
}

// Initialiser dagens data hvis den ikke findes
if (!isset($data[$date])) {
    $data[$date] = [
        'unique_visitors' => [],
        'page_views' => []
    ];
}

// Registrer unik besøgende hvis ikke set før i dag
if (!in_array($visitorHash, $data[$date]['unique_visitors'])) {
    $data[$date]['unique_visitors'][] = $visitorHash;
}

// Tæl sidevisning
if (!isset($data[$date]['page_views'][$page])) {
    $data[$date]['page_views'][$page] = 0;
}
$data[$date]['page_views'][$page]++;

// Gem data (kun de sidste 30 dage for at holde filen lille)
ksort($data);
if (count($data) > 30) {
    $data = array_slice($data, -30, null, true);
}

// Vi gemmer hashes i unique_visitors midlertidigt for at tælle dem, 
// men i den endelige rapport vil vi måske bare have antallet.
// For nu gemmer vi dem for at kunne tjekke "uniqueness" over hele dagen.

if (file_put_contents($analyticsFile, json_encode($data, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['ok' => false, 'error' => 'Kunne ikke gemme statistik']);
    exit;
}

echo json_encode(['ok' => true]);
