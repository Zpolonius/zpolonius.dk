<?php
/**
 * api/auth.php — Delt authentication helper
 * Inkluderes af alle beskyttede endpoints.
 */
require_once __DIR__ . '/config.php';

function requireAuth() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_name(SESSION_NAME);
    session_start();

    // Tjek session eksisterer og er autentificeret
    if (empty($_SESSION['authenticated'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Ikke logget ind']);
        exit;
    }

    // Tjek session ikke er udløbet
    if (time() > ($_SESSION['expires_at'] ?? 0)) {
        session_unset();
        session_destroy();
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Session udløbet — log ind igen']);
        exit;
    }

    // Forny session-timeout ved aktivitet
    $_SESSION['expires_at'] = time() + SESSION_TIMEOUT;
}
