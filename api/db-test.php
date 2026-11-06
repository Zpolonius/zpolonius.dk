<?php
/**
 * api/db-test.php — engangstest af databaseopsætningen.
 * Kræver at du er logget ind i admin. SLET FILEN, når testen er grøn.
 *
 * Tester: forbindelse, MySQL-version, strict mode, at de 6 chk_-tabeller findes,
 * og at en række kan skrives, læses og slettes igen (ingen data efterlades).
 */
require_once __DIR__ . '/auth.php';
requireAuth();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$checks = [];
$add = function (string $name, bool $ok, string $info = '') use (&$checks) {
    $checks[] = ['test' => $name, 'ok' => $ok, 'info' => $info];
};

try {
    $pdo = db();
    $add('Forbindelse', true);

    $ver = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
    $add('MySQL 8.0.16+ (CHECK-regler håndhæves)', version_compare(preg_replace('/[^0-9.].*$/', '', $ver), '8.0.16', '>='), $ver);

    $mode = (string) $pdo->query('SELECT @@SESSION.sql_mode')->fetchColumn();
    $add('Strict mode slået til', strpos($mode, 'STRICT_ALL_TABLES') !== false);

    $n = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE 'chk\\_%'")->fetchColumn();
    $add('6 chk_-tabeller findes', $n === 6, "$n fundet");

    $types = (int) $pdo->query('SELECT COUNT(*) FROM chk_finding_types')->fetchColumn();
    $add('Fund-typer indlæst', $types > 0, "$types typer");

    // Skriv → læs → slet, inde i en transaktion der rulles tilbage
    $pdo->beginTransaction();
    $pid = bin2hex(random_bytes(16));
    $pdo->prepare("INSERT INTO chk_scans (public_id, domain, url_submitted) VALUES (?, 'test.invalid', 'https://test.invalid')")->execute([$pid]);
    $st = $pdo->prepare('SELECT domain FROM chk_scans WHERE public_id = ?');
    $st->execute([$pid]);
    $add('Skriv og læs en række', $st->fetchColumn() === 'test.invalid');
    try {
        $pdo->exec("UPDATE chk_scans SET status = 'ugyldig' WHERE public_id = '$pid'");
        $add('Ugyldige værdier afvises', false, 'en ugyldig status blev accepteret');
    } catch (PDOException $e) {
        $add('Ugyldige værdier afvises', true);
    }
    $pdo->rollBack();
    $st->execute([$pid]);
    $add('Testrækken er fjernet igen', $st->fetchColumn() === false);
} catch (Throwable $e) {
    // Vis aldrig rå fejltekst (kan indeholde brugernavn/host) — kun typen
    $add('Uventet fejl', false, get_class($e) . ' — tjek DB_*-værdierne i config.php');
}

$allOk = !in_array(false, array_column($checks, 'ok'), true);
echo json_encode(['alt_ok' => $allOk, 'tests' => $checks], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
