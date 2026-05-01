<?php
/**
 * api/optimize.php — Sikker bro til Gemini AI
 * Kræver aktiv server-side session.
 */
require_once __DIR__ . '/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Kun POST tilladt']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$text = $data['text'] ?? '';
$context = $data['context'] ?? 'generelt';

if (empty($text)) {
    echo json_encode(['ok' => false, 'error' => 'Ingen tekst at optimere']);
    exit;
}

if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY) || GEMINI_API_KEY === 'INDSÆT_DIN_GEMINI_API_KEY_HER') {
    echo json_encode(['ok' => false, 'error' => 'Gemini API nøgle mangler i api/config.php']);
    exit;
}

// System prompt der sikrer vi får pæn HTML tilbage til vores editor
$prompt = "Du er en ekspert i tekstforfatning, SEO og UX-skrivning. Optimér følgende tekst til en personlig portefølje-hjemmeside. 
Konteksten er: {$context}. 

Mål:
1. Gør teksten professionel, engagerende og letlæselig (readability).
2. Optimér til SEO (brug relevante nøgleord naturligt).
3. Brug 'GEO' principper (gør det relevant for dit område/marked).
4. Formater outputtet i ren HTML, så det passer direkte ind i en Rich Text Editor.
   - Brug kun disse tags: <p>, <ul>, <ol>, <li>, <strong>, <em>, <h2>, <h3>.
   - Undlad <html>, <body> eller <head> tags.
   - Returner KUN HTML-koden, intet andet. Ingen forklaringer eller markdown-blokke (```html).

Original tekst:
" . $text;

$payload = [
    'contents' => [
        ['parts' => [['text' => $prompt]]]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 2048,
    ]
];

$ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . GEMINI_API_KEY);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode(['ok' => false, 'error' => 'Fejl fra Gemini API (HTTP ' . $http_code . ')', 'details' => $response]);
    exit;
}

$result = json_decode($response, true);
$optimized_text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

// Rens output for eventuelle markdown-indpakninger hvis Gemini fejler i instruktionen
$optimized_text = preg_replace('/^```html\s*/', '', $optimized_text);
$optimized_text = preg_replace('/```\s*$/', '', $optimized_text);

echo json_encode(['ok' => true, 'optimized' => trim($optimized_text)]);
