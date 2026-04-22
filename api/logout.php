<?php
/**
 * api/logout.php — Afslutter session sikkert
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

session_name(SESSION_NAME);
session_start();
session_unset();
session_destroy();

echo json_encode(['ok' => true]);
