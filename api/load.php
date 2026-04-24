<?php
/**
 * api/load.php — Henter content.json sikkert
 * Kræver aktiv server-side session.
 */
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

// Kræv login for at læse data via denne endpoint (valgfrit, men sikrere)
requireAuth();

if (!file_exists(CONTENT_FILE)) {
    // Hvis filen ikke findes, returner en tom men korrekt struktur
    echo json_encode([
        'site' => ['name' => 'Zacharias Polonius', 'tagline' => ''],
        'hero' => ['role' => '', 'cover' => 'assets/cover.webp', 'status' => '', 'intro' => ''],
        'specialer' => [],
        'bento' => [],
        'om' => ['bio' => '', 'photo' => '', 'sprog' => [], 'interesser' => []],
        'projects' => [],
        'cv' => ['jobs' => [], 'education' => [], 'recommendations' => []]
    ]);
    exit;
}

$json = file_get_contents(CONTENT_FILE);
echo $json;
