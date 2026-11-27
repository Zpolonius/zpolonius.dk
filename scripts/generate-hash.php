<?php
/**
 * scripts/generate-hash.php — genererer ADMIN_PASSWORD_HASH til api/config.php.
 *
 * Kun til kørsel fra kommandolinjen. Kodeordet læses fra STDIN, så det aldrig
 * skrives i en fil. Scriptet må ikke uploades til serveren.
 *
 * Brug:
 *   php scripts/generate-hash.php
 *   docker run --rm -it -v "$PWD/scripts:/s" php:8-cli php /s/generate-hash.php
 */

if (PHP_SAPI !== 'cli') {
    exit("Dette script kan kun køres fra kommandolinjen.\n");
}

// Skjul input, hvis vi kører i en terminal med stty (Linux/macOS). Ellers læses almindeligt.
$skjul = function_exists('posix_isatty') && posix_isatty(STDIN) && function_exists('shell_exec');
if ($skjul) {
    fwrite(STDERR, 'Indtast admin-kodeord: ');
    @shell_exec('stty -echo 2>/dev/null');
}

$linje = fgets(STDIN);

if ($skjul) {
    @shell_exec('stty echo 2>/dev/null');
    fwrite(STDERR, "\n");
}

if ($linje === false) {
    fwrite(STDERR, "Fejl: der kom intet input på STDIN.\n");
    exit(1);
}

// Fjern kun linjeskift, så mellemrum i kodeordet bevares.
$password = rtrim($linje, "\r\n");

if ($password === '') {
    fwrite(STDERR, "Fejl: kodeordet må ikke være tomt.\n");
    exit(1);
}

echo password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]) . "\n";
