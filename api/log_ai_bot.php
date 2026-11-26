<?php
/**
 * api/log_ai_bot.php — Logger crawl-besøg fra kendte AI/LLM-bots.
 *
 * AI-crawlere kører ikke JavaScript, så api/track.php (som kaldes via JS i
 * browseren) ser dem aldrig. Denne fil inkluderes øverst i server-renderede
 * PHP-sider (fx detail.php) og registrerer bot-crawls direkte serverside i
 * data/analytics.json under nøglen 'ai_bots'.
 *
 * Fejler altid lydløst — må aldrig forstyrre selve sidevisningen.
 */

(function () {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($ua === '') return;

    // User-agent-fragment => pænt label
    $bots = [
        'GPTBot'             => 'ChatGPT (GPTBot)',
        'OAI-SearchBot'      => 'ChatGPT Search',
        'ChatGPT-User'       => 'ChatGPT (bruger)',
        'ClaudeBot'          => 'Claude (ClaudeBot)',
        'Claude-Web'         => 'Claude (web)',
        'anthropic-ai'       => 'Claude',
        'PerplexityBot'      => 'Perplexity',
        'Perplexity-User'    => 'Perplexity (bruger)',
        'Google-Extended'    => 'Gemini (Google-Extended)',
        'Applebot-Extended'  => 'Apple Intelligence',
        'CCBot'              => 'Common Crawl (CCBot)',
        'Amazonbot'          => 'Amazon',
        'Bytespider'         => 'ByteDance/Doubao',
        'cohere-ai'          => 'Cohere',
        'Meta-ExternalAgent' => 'Meta AI',
    ];

    $label = null;
    foreach ($bots as $needle => $name) {
        if (stripos($ua, $needle) !== false) { $label = $name; break; }
    }
    if ($label === null) return; // ikke en AI-bot — gør intet

    require_once __DIR__ . '/analytics_store.php';

    $file = __DIR__ . '/../data/analytics.json';
    $date = date('Y-m-d');

    zp_update_analytics($file, function (array &$data) use ($date, $label) {
        zp_ensure_analytics_day($data, $date);
        if (!isset($data[$date]['ai_bots'][$label])) {
            $data[$date]['ai_bots'][$label] = 0;
        }
        $data[$date]['ai_bots'][$label]++;
    });
})();
