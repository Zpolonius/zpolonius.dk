<?php
/**
 * api/public_content.php — Offentlig udgave af content.json (issue #14)
 *
 * Fjerner alle listeelementer med "visible": false (kladder og skjulte
 * anbefalinger m.m.), så "skjul" i admin også betyder "ikke offentligt".
 * Ingen output — inkluderes af api/content.php og tests.
 */

function zp_is_list(array $a) {
    $i = 0;
    foreach ($a as $k => $_) {
        if ($k !== $i++) return false;
    }
    return true;
}

function zp_public_content($node) {
    if (!is_array($node)) return $node;

    if (zp_is_list($node)) {
        $out = [];
        foreach ($node as $item) {
            // Samme regel som frontenden: kun strengt false skjuler
            if (is_array($item) && array_key_exists('visible', $item) && $item['visible'] === false) continue;
            $out[] = zp_public_content($item);
        }
        return $out;
    }

    foreach ($node as $k => $v) {
        $node[$k] = zp_public_content($v);
    }
    return $node;
}
