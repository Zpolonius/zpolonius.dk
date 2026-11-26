<?php
/**
 * api/track.php — Registrerer sidevisninger og unikke besøgende (privatlivsvenlig)
 */
header('Content-Type: application/json');

require_once __DIR__ . '/analytics_store.php';

// Definer sti til analytics filen
$analyticsFile = __DIR__ . '/../data/analytics.json';

// --- RATE LIMITING ---
// Max 60 tracking-kald per IP per minut
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rateDir = sys_get_temp_dir() . '/zp_track_rate/';
if (!is_dir($rateDir)) {
    @mkdir($rateDir, 0700, true);
}
$rateFile = $rateDir . hash('sha256', $ip . 'zp_rate_2026') . '.json';
$now = time();
$rateWindow = 60;
$rateMaxReqs = 60;

$rateFp = @fopen($rateFile, 'c+');
if ($rateFp && flock($rateFp, LOCK_EX)) {
    rewind($rateFp);
    $rateRaw = stream_get_contents($rateFp);
    $attempts = json_decode($rateRaw ?: '[]', true);
    if (!is_array($attempts)) {
        $attempts = [];
    }
    $attempts = array_values(array_filter($attempts, fn($t) => is_int($t) && $t > $now - $rateWindow));
    if (count($attempts) >= $rateMaxReqs) {
        flock($rateFp, LOCK_UN);
        fclose($rateFp);
        http_response_code(429);
        echo json_encode(['ok' => false, 'error' => 'For mange forespørgsler']);
        exit;
    }
    $attempts[] = $now;
    rewind($rateFp);
    ftruncate($rateFp, 0);
    fwrite($rateFp, json_encode($attempts));
    fflush($rateFp);
    flock($rateFp, LOCK_UN);
    fclose($rateFp);
} elseif ($rateFp) {
    fclose($rateFp);
}

// Hent input data
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
$page  = $input['page'] ?? null;
$event = $input['event'] ?? null;

// Valider input. Kun tekststrenge accepteres; alt andet ignoreres.
// Sidenavnet er browserens location.pathname, fx "/" eller "/projects/min-case".
// Alt andet afvises. FILTER_SANITIZE_URL alene lod "<", ">" og anførselstegn
// passere, så en besøgende kunne gemme HTML, som blev kørt i admin-statistikken.
$page = (is_string($page) && preg_match('#^/[A-Za-z0-9._~%/-]{0,200}$#', $page)) ? $page : null;
// Event-navne svarer til sitets data-track-attributter (fx "contact-open-header"):
// små bogstaver, tal og bindestreg, højst 64 tegn. Andet afvises i stedet for at
// blive "renset" — FILTER_SANITIZE_STRING er forældet siden PHP 8.1 og lavede
// fx "<script>x</script>" om til en falsk event "x".
$event = (is_string($event) && preg_match('/^[a-z0-9][a-z0-9-]{0,63}$/', $event)) ? $event : null;

// Hent besøgendes data til hashing (anonymisering)
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$date = date('Y-m-d');

// Skab en anonym hash for dagen (IP + UA + Salt + Dato)
// Vi gemmer aldrig den rå IP.
$visitorHash = hash('sha256', $ip . $ua . 'zp_salt_2026' . $date);

$maxUniqueVisitorsPerDay = 5000;
$maxReferrersPerDay = 200;

$saved = zp_update_analytics($analyticsFile, function (array &$data) use (
    $date,
    $visitorHash,
    $ip,
    $ua,
    $page,
    $event,
    $maxUniqueVisitorsPerDay,
    $maxReferrersPerDay
) {
    zp_ensure_analytics_day($data, $date);

    // Registrer unik besøgende hvis ikke set før i dag (med dagligt loft mod ubegrænset vækst)
    if (
        !in_array($visitorHash, $data[$date]['unique_visitors'], true) &&
        count($data[$date]['unique_visitors']) < $maxUniqueVisitorsPerDay
    ) {
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
                $refHost = str_replace('www.', '', $refHost);

                // Er kilden en AI-assistent / LLM? Så labeller vi trafikken særskilt.
                // (Dette er rigtige mennesker, der klikker et link i et AI-svar.)
                $aiSources = [
                    'chatgpt.com'           => 'ChatGPT',
                    'chat.openai.com'       => 'ChatGPT',
                    'perplexity.ai'         => 'Perplexity',
                    'gemini.google.com'     => 'Gemini',
                    'claude.ai'             => 'Claude',
                    'copilot.microsoft.com' => 'Microsoft Copilot',
                    'you.com'               => 'You.com',
                    'poe.com'               => 'Poe',
                    'phind.com'             => 'Phind',
                    'grok.com'              => 'Grok',
                    'x.ai'                  => 'Grok',
                ];
                foreach ($aiSources as $needle => $label) {
                    if (strpos($refHost, $needle) !== false) {
                        if (!isset($data[$date]['ai_referrers'][$label])) {
                            $data[$date]['ai_referrers'][$label] = 0;
                        }
                        $data[$date]['ai_referrers'][$label]++;
                        break;
                    }
                }

                $ref = $refHost;
                // Hvis det er dit eget domæne, tæl det som interne links eller 'Direkte'
                if (isset($_SERVER['HTTP_HOST']) && strpos($ref, $_SERVER['HTTP_HOST']) !== false) {
                    $ref = 'Intern navigation';
                }
            }
        }
        if (!isset($data[$date]['referrers'][$ref]) && count($data[$date]['referrers']) >= $maxReferrersPerDay) {
            $ref = 'Andre';
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
});

if (!$saved) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Kunne ikke gemme statistik']);
    exit;
}

echo json_encode(['ok' => true]);
