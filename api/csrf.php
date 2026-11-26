<?php
/**
 * api/csrf.php — Genererer og returnerer et CSRF-token
 */
require_once __DIR__ . '/session.php';
start_session();

header('Content-Type: application/json');

// Generer token hvis det ikke findes
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo json_encode([
    'ok' => true,
    'token' => $_SESSION['csrf_token']
]);
