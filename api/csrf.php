<?php
/**
 * api/csrf.php — Genererer og returnerer et CSRF-token
 */
session_start();

header('Content-Type: application/json');

// Generer token hvis det ikke findes
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo json_encode([
    'ok' => true,
    'token' => $_SESSION['csrf_token']
]);
