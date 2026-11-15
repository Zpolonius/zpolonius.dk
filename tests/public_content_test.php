<?php
/**
 * tests/public_content_test.php — issue #14
 * Kør: php tests/public_content_test.php   (exit 0 = alt bestået)
 *
 * Sikrer at skjult indhold (visible:false) aldrig sendes til offentligheden,
 * og at data/ ikke serverer filer direkte.
 */

$root = dirname(__DIR__);
$fails = 0;
function check($cond, $msg) {
    global $fails;
    echo ($cond ? "PASS " : "FAIL ") . $msg . PHP_EOL;
    if (!$cond) $fails++;
}

// ---- 1. Filtrering af skjulte elementer ----
$lib = $root . '/api/public_content.php';
check(file_exists($lib), 'api/public_content.php findes');
if (file_exists($lib)) {
    require_once $lib;
    $in = [
        'site' => ['name' => 'Z', 'visible' => false], // objekt (ikke liste) røres ikke
        'projects' => [
            ['id' => 'a', 'visible' => true],
            ['id' => 'b', 'visible' => false],
            ['id' => 'c'],
            ['id' => 'd', 'visible' => 'false'],      // kun streng false skjuler (som frontenden)
        ],
        'cv' => [
            'recommendations' => [
                ['id' => 'anbefaling-skjult', 'visible' => false, 'text' => 'HEMMELIG'],
                ['id' => 'r2', 'visible' => true],
            ],
        ],
        'tags' => ['x', 'y'],
    ];
    $out = zp_public_content($in);
    $ids = array_column($out['projects'], 'id');
    check($ids === ['a', 'c', 'd'], 'skjult projekt fjernes, rækkefølge og index bevares (' . implode(',', $ids) . ')');
    check(array_column($out['cv']['recommendations'], 'id') === ['r2'], 'skjult anbefaling fjernes (indlejret)');
    check(strpos(json_encode($out), 'HEMMELIG') === false, 'skjult tekst findes ikke i output');
    check(isset($out['site']['name']), 'ikke-liste-objekter røres ikke');
    check($out['tags'] === ['x', 'y'], 'skalarlister er uændrede');
    check(json_encode($out['projects'])[0] === '[', 'lister forbliver JSON-arrays efter filtrering');
}

// ---- 2. data/ må ikke servere filer direkte ----
$ht = @file_get_contents($root . '/data/.htaccess') ?: '';
check(!preg_match('/Require all granted|Allow from all/i', $ht), 'data/.htaccess giver ingen læseadgang');
check((bool) preg_match('/Require all denied/i', $ht), 'data/.htaccess nægter alt');

// ---- 3. Ingen backup-/kladde-JSON i data/ ----
$extra = array_values(array_filter(scandir($root . '/data'), function ($f) {
    return preg_match('/\.json$/', $f) && $f !== 'content.json';
}));
check($extra === [], 'ingen ekstra JSON-filer i data/ (' . implode(', ', $extra) . ')');

// ---- 4. Frontend henter ikke data/content.json direkte ----
foreach (['js/main.js', 'about.php'] as $f) {
    $src = file_get_contents($root . '/' . $f);
    check(strpos($src, "fetch('data/content.json") === false, "$f henter ikke data/content.json direkte");
}

echo $fails ? "\n$fails fejl\n" : "\nAlle tests bestået\n";
exit($fails ? 1 : 0);
