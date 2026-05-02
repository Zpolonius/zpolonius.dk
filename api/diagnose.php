<?php
/**
 * api/diagnose.php — Midlertidig diagnose-fil
 * VIGTIGT: Slet denne fil fra serveren efter test!
 * Åbn i browseren: https://zpolonius.dk/api/diagnose.php?token=zp2024
 */

// Simpel token-beskyttelse
if (($_GET['token'] ?? '') !== 'zp2024') {
    http_response_code(403);
    die('Adgang nægtet');
}

require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=UTF-8');

$to   = defined('MAIL_TO')   ? MAIL_TO   : 'zacharias@polonius.dk';
$from = defined('MAIL_FROM') ? MAIL_FROM : 'kontakt@zpolonius.dk';

echo "<h2>🔍 Mail Diagnose — zpolonius.dk</h2>";
echo "<pre>";

// 1. PHP info
echo "PHP version: " . phpversion() . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'ukendt') . "\n\n";

// 2. mail() funktion tilgængelig?
echo "mail() funktion: " . (function_exists('mail') ? '✅ tilgængelig' : '❌ IKKE tilgængelig') . "\n";

// 3. MAIL_FROM konto
echo "MAIL_FROM: $from\n";
echo "MAIL_TO:   $to\n\n";

// 4. Sendmail path
$sendmailPath = ini_get('sendmail_path');
echo "sendmail_path: " . ($sendmailPath ?: '(tom — bruger standard)') . "\n";

// 5. Prøv at sende en test-mail
$subject = "[TEST] Diagnose mail fra zpolonius.dk - " . date('H:i:s');
$body    = "Dette er en test-mail sendt fra api/diagnose.php\n\nTidspunkt: " . date('Y-m-d H:i:s');

$headers   = [];
$headers[] = "From: Test <{$from}>";
$headers[] = "Reply-To: {$from}";
$headers[] = "Content-Type: text/plain; charset=UTF-8";

echo "\nForsøger at sende test-mail til: $to\n";
$result = @mail($to, $subject, $body, implode("\r\n", $headers), "-f {$from}");

echo "mail() returnerede: " . ($result ? '✅ true (accepteret af mailserver)' : '❌ false (afvist)') . "\n";

// 6. Tjek PHP error_log
echo "\nPHP error_log placering: " . (ini_get('error_log') ?: '(standard — tjek serverlog)') . "\n";

echo "</pre>";
echo "<hr>";
echo "<p style='color:red;font-weight:bold;'>⚠️ HUSK: Slet denne fil (api/diagnose.php) fra serveren efter test!</p>";
echo "<p>Hvis 'mail() returnerede: true' men du ikke modtager mailen, sidder problemet i Simply's mailserver eller spam-filtre.<br>";
echo "Hvis 'mail() returnerede: false', er PHP's mail-funktion blokeret på serveren.</p>";
