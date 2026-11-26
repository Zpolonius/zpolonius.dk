<?php
/**
 * api/session.php — Fælles session-starter med sikre cookie-flags
 */

function start_session(?string $sessionName = null): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.cookie_httponly', 1);
    // Kun secure cookie hvis vi kører over HTTPS
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    ini_set('session.cookie_secure', $isHttps ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');

    if ($sessionName !== null && $sessionName !== '') {
        session_name($sessionName);
    }

    session_start();
}
