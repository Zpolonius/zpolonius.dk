<?php
/**
 * api/analytics.php — Henter statistik til admin panelet
 * Kræver aktiv server-side session.
 */
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

requireAuth();

$analyticsFile = __DIR__ . '/../data/analytics.json';

if (!file_exists($analyticsFile)) {
    echo json_encode(['ok' => true, 'data' => []]);
    exit;
}

$data = json_decode(file_get_contents($analyticsFile), true);

// Forbered data til admin panelet (vi behøver ikke sende de rå hashes)
$report = [];
foreach ($data as $date => $stats) {
    $report[$date] = [
        'unique_visitors' => count($stats['unique_visitors']),
        'page_views' => $stats['page_views'],
        'locations' => $stats['locations'] ?? []
    ];
}

echo json_encode(['ok' => true, 'data' => $report]);
