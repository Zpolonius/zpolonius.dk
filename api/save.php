<?php
/**
 * api/save.php — Gemmer content.json
 * Kræver aktiv server-side session.
 */
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

function extractAssetPaths($node) {
    if (is_string($node)) {
        $trimmed = ltrim($node, './');
        return (strncmp($trimmed, 'assets/', 7) === 0 && strlen($trimmed) > 7)
            ? [$trimmed]
            : [];
    }
    if (!is_array($node)) return [];
    $paths = [];
    foreach ($node as $value) {
        $paths = array_merge($paths, extractAssetPaths($value));
    }
    return array_unique($paths);
}

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

// Saml forældreløse asset-stier FØR vi skriver ny content.json
$orphan_paths = [];
if (ASSETS_DIR !== false && file_exists(CONTENT_FILE)) {
    $old_raw  = file_get_contents(CONTENT_FILE);
    $old_data = json_decode($old_raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($old_data)) {
        $orphan_paths = array_diff(
            extractAssetPaths($old_data),
            extractAssetPaths($data)
        );
    }
}

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents(CONTENT_FILE, $json) === false) {
    echo json_encode(['ok' => false, 'error' => 'Kunne ikke skrive filen. Tjek mapperettigheder.']); exit;
}

// Slet forældreløse filer — kun efter vellykket write
foreach ($orphan_paths as $rel_path) {
    $abs_path = realpath(__DIR__ . '/../' . $rel_path);
    if ($abs_path === false) continue;
    if (strncmp($abs_path, ASSETS_DIR, strlen(ASSETS_DIR)) !== 0) {
        error_log("save.php: BLOCKED traversal: {$rel_path}");
        continue;
    }
    if (is_dir($abs_path)) continue;
    if (!unlink($abs_path)) {
        error_log("save.php: unlink fejlede: {$abs_path}");
    }
}

echo json_encode(['ok' => true, 'saved' => date('Y-m-d H:i:s')]);
