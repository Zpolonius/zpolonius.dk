<?php
/**
 * api/analytics_store.php — Trådsikker læsning og skrivning af data/analytics.json
 *
 * Bruger flock(LOCK_EX) omkring hele read-modify-write-cyklussen og afbryder
 * uden at overskrive filen, hvis eksisterende JSON ikke kan parses.
 */

function zp_ensure_analytics_day(array &$data, string $date): void {
    if (!isset($data[$date]) || !is_array($data[$date])) {
        $data[$date] = [
            'unique_visitors' => [],
            'page_views'      => [],
            'locations'       => [],
            'referrers'       => [],
            'ai_referrers'    => [],
            'devices'         => [],
            'events'          => [],
            'ai_bots'         => [],
        ];
        return;
    }
    if (!isset($data[$date]['ai_bots']) || !is_array($data[$date]['ai_bots'])) {
        $data[$date]['ai_bots'] = [];
    }
}

function zp_update_analytics(string $file, callable $mutator): bool {
    $dir = dirname($file);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $fp = @fopen($file, 'c+');
    if (!$fp) {
        return false;
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }

    rewind($fp);
    $raw = stream_get_contents($fp);

    if ($raw === false) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return false;
    }

    if (trim($raw) === '') {
        $data = [];
    } else {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            // Afbryd uden at overskrive, så korrupt eller halvskrevet data ikke sletter historik.
            flock($fp, LOCK_UN);
            fclose($fp);
            return false;
        }
        $data = $decoded;
    }

    $mutator($data);

    // Behold kun de seneste 30 dage for at holde filstørrelsen nede
    ksort($data);
    if (count($data) > 30) {
        $data = array_slice($data, -30, null, true);
    }

    $encoded = json_encode($data, JSON_PRETTY_PRINT);
    if ($encoded === false) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return false;
    }

    rewind($fp);
    ftruncate($fp, 0);
    $written = fwrite($fp, $encoded);
    fflush($fp);

    flock($fp, LOCK_UN);
    fclose($fp);

    return $written !== false;
}
