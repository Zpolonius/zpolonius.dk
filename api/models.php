<?php
/**
 * api/models.php — Henter tilgængelige Gemini-modeller direkte fra Google AI Studio
 * Kræver aktiv server-side session.
 */
require_once __DIR__ . '/auth.php';
requireAuth();

header('Content-Type: application/json');

if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY) || GEMINI_API_KEY === 'INDSÆT_DIN_GEMINI_API_KEY_HER') {
    echo json_encode(['ok' => false, 'error' => 'Gemini API nøgle mangler i api/config.php']);
    exit;
}

$ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models?key=' . GEMINI_API_KEY);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode(['ok' => false, 'error' => 'Kunne ikke hente modeller fra Google API (HTTP ' . $http_code . ')']);
    exit;
}

$data = json_decode($response, true);
$models = [];
if (isset($data['models'])) {
    foreach ($data['models'] as $m) {
        // Vi er kun interesserede i modeller der understøtter tekst-generering (generateContent)
        if (in_array('generateContent', $m['supportedGenerationMethods'] ?? [])) {
            $models[] = [
                'id' => str_replace('models/', '', $m['name']),
                'name' => $m['displayName']
            ];
        }
    }
}

// Sorter modellerne så de nyeste (højeste versionsnummer eller seneste udgivelser) ofte ender øverst
usort($models, function($a, $b) {
    return strnatcasecmp($b['id'], $a['id']);
});

echo json_encode(['ok' => true, 'models' => $models]);
