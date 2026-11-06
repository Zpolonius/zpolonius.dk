<?php
declare(strict_types=1);

/**
 * api/db.php — fælles databaseforbindelse (MySQL via PDO)
 *
 * Brug:  require_once __DIR__ . '/db.php';
 *        $stmt = db()->prepare('SELECT * FROM chk_scans WHERE public_id = ?');
 *        $stmt->execute([$publicId]);
 *
 * Login-oplysningerne (DB_HOST, DB_NAME, DB_USER, DB_PASS) står i config.php,
 * som er git-ignoreret og beskyttet af api/.htaccess — ligesom admin-koden.
 * Kalder man filen direkte i browseren, sker der intet (den definerer kun en funktion).
 */
require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new RuntimeException('Database er ikke sat op: DB_HOST, DB_NAME, DB_USER og DB_PASS mangler i api/config.php');
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // fejl stopper koden i stedet for at blive ignoreret
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,                  // ægte prepared statements — beskytter mod SQL-injection
    ]);

    // Strict mode: ugyldige værdier afvises i stedet for stille at blive gemt som tomme.
    // Uden den kan fx et køb gemmes uden samtykke til fortrydelsesret.
    $pdo->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

    return $pdo;
}
