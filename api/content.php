<?php
/**
 * api/content.php — Offentligt endpoint til sidens indhold (issue #14)
 *
 * data/ er lukket for direkte adgang, så frontenden henter indhold her.
 * Skjulte elementer (visible:false) fjernes, før noget sendes.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/public_content.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Content-Type-Options: nosniff');

$raw  = file_exists(CONTENT_FILE) ? file_get_contents(CONTENT_FILE) : false;
$data = $raw !== false ? json_decode($raw, true) : null;

if (!is_array($data)) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'Indhold utilgængeligt']);
    exit;
}

echo json_encode(zp_public_content($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
