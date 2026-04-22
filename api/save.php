<?php
/**
 * api/save.php — Gemmer content.json
 * Kræver aktiv server-side session.
 */
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); exit;
}

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Kun POST tilladt']); exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['ok' => false, 'error' => 'Ugyldigt JSON']); exit;
}

if (!is_dir(dirname(CONTENT_FILE))) {
    mkdir(dirname(CONTENT_FILE), 0755, true);
}

// Gem backup
if (file_exists(CONTENT_FILE)) {
    copy(CONTENT_FILE, CONTENT_FILE . '.bak');
}

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents(CONTENT_FILE, $json) === false) {
    echo json_encode(['ok' => false, 'error' => 'Kunne ikke skrive filen. Tjek mapperettigheder.']); exit;
}

echo json_encode(['ok' => true, 'saved' => date('Y-m-d H:i:s')]);
