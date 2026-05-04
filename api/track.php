<?php
/**
 * api/track.php — Registrerer sidevisninger og unikke besøgende (privatlivsvenlig)
 */
header('Content-Type: application/json');

// Definer sti til analytics filen
$analyticsFile = __DIR__ . '/../data/analytics.json';

// Hent input data
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
$page  = $input['page'] ?? null;
$event = $input['event'] ?? null;

// Rens strings for sikkerhed
if ($page)  $page  = filter_var($page, FILTER_SANITIZE_URL);
if ($event) $event = filter_var($event, FILTER_SANITIZE_STRING);

// Hent besøgendes data til hashing (anonymisering)
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$date = date('Y-m-d');

// Skab en anonym hash for dagen (IP + UA + Salt + Dato)
// Vi gemmer aldrig den rå IP.
$visitorHash = hash('sha256', $ip . $ua . 'zp_salt_2026' . $date);

// Hent eksisterende data eller start forfra
if (file_exists($analyticsFile)) {
    $data = json_decode(file_get_contents($analyticsFile), true);
} else {
    $data = [];
}

// Initialiser dagens data hvis den ikke findes
if (!isset($data[$date])) {
    $data[$date] = [
        'unique_visitors' => [],
        'page_views' => [],
        'locations' => [],
        'referrers' => [],
        'devices' => [],
        'events' => []
    ];
}

// Registrer unik besøgende hvis ikke set før i dag
if (!in_array($visitorHash, $data[$date]['unique_visitors'])) {
    $data[$date]['unique_visitors'][] = $visitorHash;

    // 1. Hent lokation (kun for nye unikke besøgende)
    if ($ip !== '127.0.0.1' && $ip !== '::1' && $ip !== '0.0.0.0') {
        try {
            $locRaw = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city");
            if ($locRaw) {
                $locData = json_decode($locRaw, true);
                if (($locData['status'] ?? '') === 'success') {
                    $locKey = $locData['country'] . ($locData['city'] ? ' (' . $locData['city'] . ')' : '');
                    if (!isset($data[$date]['locations'][$locKey])) {
                        $data[$date]['locations'][$locKey] = 0;
                    }
                    $data[$date]['locations'][$locKey]++;
                }
            }
        } catch (Exception $e) {}
    }

    // 2. Hent Referrer (Hvor kommer de fra?)
    $ref = $_SERVER['HTTP_REFERER'] ?? 'Direkte / Ukendt';
    if ($ref !== 'Direkte / Ukendt') {
        $refHost = parse_url($ref, PHP_URL_HOST);
        if ($refHost) {
            // Fjern 'www.' for at gruppere dem
            $ref = str_replace('www.', '', $refHost);
            // Hvis det er dit eget domæne, tæl det som interne links eller 'Direkte'
            if (isset($_SERVER['HTTP_HOST']) && strpos($ref, $_SERVER['HTTP_HOST']) !== false) {
                $ref = 'Intern navigation';
            }
        }
    }
    if (!isset($data[$date]['referrers'][$ref])) {
        $data[$date]['referrers'][$ref] = 0;
    }
    $data[$date]['referrers'][$ref]++;

    // 3. Hent Enhedstype
    $device = 'Desktop';
    if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
        $device = 'Tablet';
    } else if (preg_match('/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/i', $ua)) {
        $device = 'Mobil';
    }
    if (!isset($data[$date]['devices'][$device])) {
        $data[$date]['devices'][$device] = 0;
    }
    $data[$date]['devices'][$device]++;
}

// Tæl sidevisning (hvis det er en page ping)
if ($page) {
    if (!isset($data[$date]['page_views'][$page])) {
        $data[$date]['page_views'][$page] = 0;
    }
    $data[$date]['page_views'][$page]++;
}

// Tæl event (hvis det er en event ping)
if ($event) {
    if (!isset($data[$date]['events'][$event])) {
        $data[$date]['events'][$event] = 0;
    }
    $data[$date]['events'][$event]++;
}

// Gem data (kun de sidste 30 dage for at holde filen lille)
ksort($data);
if (count($data) > 30) {
    $data = array_slice($data, -30, null, true);
}

// Vi gemmer hashes i unique_visitors midlertidigt for at tælle dem, 
// men i den endelige rapport vil vi måske bare have antallet.
// For nu gemmer vi dem for at kunne tjekke "uniqueness" over hele dagen.

if (file_put_contents($analyticsFile, json_encode($data, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['ok' => false, 'error' => 'Kunne ikke gemme statistik']);
    exit;
}

echo json_encode(['ok' => true]);
