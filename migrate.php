<?php
/**
 * migrate.php — WordPress til content.json migreringsscript
 * Version 2 — forbedret periode-håndtering, afsnit og billedliste
 *
 * BRUG:
 * 1. Upload til WordPress-roden: public_html/migrate.php
 *    (samme niveau som wp-config.php)
 * 2. Åbn: zpolonius.dk/migrate.php
 * 3. Download content.json
 * 4. Upload til: test.zpolonius.dk/data/content.json
 * 5. SLET denne fil med det samme!
 */

$wp_load = dirname(__FILE__) . '/wp-load.php';
if (!file_exists($wp_load)) {
    die('<h2>Fejl</h2><p>Kunne ikke finde wp-load.php. Flyt migrate.php til samme mappe som WordPress (public_html/).</p>');
}
require_once($wp_load);

/* ---- HJÆLPEFUNKTIONER ---- */

function html_to_body($html) {
    $html = preg_replace('/<\/p>\s*<p[^>]*>/i', "\n\n", $html);
    $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
    $html = preg_replace('/<h[2-6][^>]*>(.*?)<\/h[2-6]>/is', "\n\n$1\n\n", $html);
    $html = preg_replace('/<li[^>]*>(.*?)<\/li>/is', "• $1\n", $html);
    $text = wp_strip_all_tags($html);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
    return trim($text);
}

function make_excerpt($html, $words = 35) {
    $text = wp_strip_all_tags($html);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/\s+/', ' ', trim($text));
    $arr  = explode(' ', $text);
    return count($arr) > $words
        ? implode(' ', array_slice($arr, 0, $words)) . '...'
        : $text;
}

function format_period($post) {
    $content = $post->post_content . ' ' . $post->post_title;
    if (preg_match('/(\d{4})\s*[–—-]+\s*(\d{4}|nu|now|present)/i', $content, $m)) {
        $end = in_array(strtolower($m[2]), ['nu','now','present']) ? 'Nu' : $m[2];
        return $m[1] . ' — ' . $end;
    }
    $start = date('Y', strtotime($post->post_date));
    $mod   = date('Y', strtotime($post->post_modified));
    return ($start !== $mod) ? "$start — $mod" : $start;
}

function guess_company($text) {
    $text = strtolower($text);
    $map  = [
        'bring'     => 'Bring',
        'altapay'   => 'Altapay',
        'shopbox'   => 'Shopbox',
        'mybizz'    => 'MyBizz AI',
        'espresso'  => 'Espresso House',
        'coffee'    => 'Espresso House',
        'farum'     => 'Farum Kulturhus',
        'kulturhus' => 'Farum Kulturhus',
        'dsu'       => 'DSU',
        'rust'      => 'Rust',
    ];
    foreach ($map as $kw => $name) {
        if (strpos($text, $kw) !== false) return $name;
    }
    return '';
}

function guess_institution($text) {
    $text = strtolower($text);
    if (strpos($text, 'scrum') !== false)                                          return 'Scrum.org';
    if (strpos($text, 'serviceøkonom') !== false || strpos($text, 'serviceokonom') !== false) return 'Erhvervsakademi';
    if (strpos($text, 'eventkoordinator') !== false)                               return 'Erhvervsskole';
    if (strpos($text, 'folkeuniversitet') !== false)                               return 'Folkeuniversitetet';
    return '';
}

/* ---- KATEGORI-MAPPING ---- */

$category_mapping = [
    'ansaettelser'       => 'work',
    'ai'                 => 'ai',
    'vibe-coding'        => 'ai',
    'frivilligt-arbejde' => 'volunteer',
    'uddannelse'         => 'education',
    'anbefalinger'       => 'recommendation',
    'info'               => 'about',
    'uncategorized'      => 'general',
];

/* ---- HENT WORDPRESS DATA ---- */

$all_posts = get_posts([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

$all_pages = get_posts([
    'post_type'      => 'page',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
]);

/* ---- OUTPUT STRUKTUR ---- */

$output = [
    'site' => [
        'name'         => get_bloginfo('name'),
        'tagline'      => get_bloginfo('description'),
        'email'        => 'zacharias@polonius.dk',
        'phone'        => '3068 7041',
        'location'     => 'Bjæverskov, Danmark',
        'accent_color' => '#418FFF',
        'social'       => [
            'linkedin'  => 'https://www.linkedin.com/in/zpolonius/',
            'instagram' => 'https://www.instagram.com/zackp91/',
            'facebook'  => 'https://www.facebook.com/zpolonius',
        ],
    ],
    'hero' => [
        'role'   => 'Technical Account Manager · Bring',
        'cover'  => 'assets/cover.webp',
        'status' => 'Tilgængelig for nye muligheder',
        'intro'  => 'De små detaljer leder til store forandringer — inden for AI, teknisk support og kundesuccess.',
    ],
    'specialer' => [
        ['id'=>'checkout',    'label'=>'Checkout-optimering',        'color'=>'blue',  'icon'=>'◈', 'desc'=>'Optimering af checkout-flow og konverteringsrate for e-commerce kunder.'],
        ['id'=>'konvertering','label'=>'Konverteringsoptimering',     'color'=>'blue',  'icon'=>'↑', 'desc'=>'Data-drevet tilgang til at øge konverteringsrater og forbedre den digitale kunderejse.'],
        ['id'=>'tam',         'label'=>'Technical Account Management','color'=>'green', 'icon'=>'⬡', 'desc'=>'Teknisk rådgivning og bindeleddet mellem forretning og IT.'],
        ['id'=>'ai',          'label'=>'AI & Vibe Coding',            'color'=>'blue',  'icon'=>'◆', 'desc'=>'Bygger AI-drevne løsninger med moderne AI-værktøjer.'],
        ['id'=>'ledelse',     'label'=>'Ledelse & organisation',      'color'=>'green', 'icon'=>'◉', 'desc'=>'Erfaring med teamledelse og organisationsudvikling.'],
        ['id'=>'support',     'label'=>'Teknisk support',             'color'=>'green', 'icon'=>'◎', 'desc'=>'10+ års erfaring med teknisk support og kundekommunikation.'],
    ],
    'bento' => [
        ['label'=>'Erfaring',        'value'=>'10+',                      'unit'=>'år','sub'=>'Tech, support og ledelse',    'desc'=>'Fra coffee shop manager til technical account manager.',   'accent'=>'blue', 'detail'=>'erfaring'],
        ['label'=>'Nuværende rolle', 'value'=>'Technical Account Manager',             'sub'=>'Bring · sep. 2023 — nu',       'desc'=>'Teknisk rådgiver for e-commerce kunder og partnere.',      'accent'=>'none', 'detail'=>'rolle'],
        ['label'=>'Certificering',   'value'=>'PSPO I',                                'sub'=>'Professional Scrum Product Owner','desc'=>'Agil produktudvikling og backlog-styring.',             'accent'=>'green','detail'=>'pspo'],
        ['label'=>'Uddannelse',      'value'=>'Serviceøkonom',                         'sub'=>'+ Eventkoordinator',            'desc'=>'Speciale i service management og kommunikation.',          'accent'=>'none', 'detail'=>'uddannelse'],
        ['label'=>'Speciale',        'value'=>'Checkout & AI',                         'sub'=>'Konvertering og vibe coding',   'desc'=>'Optimerer checkout-flows og bygger AI-drevne løsninger.', 'accent'=>'blue', 'detail'=>'ai'],
        ['label'=>'Om mig',          'value'=>'Zacharias, 33',                         'sub'=>'Bjæverskov · Gift · To sønner', 'desc'=>'Nysgerrig og altid på udkig efter den smarte løsning.',   'accent'=>'none', 'detail'=>'om'],
    ],
    'om' => [
        'bio'        => '',
        'photo'      => 'assets/photo.jpg',
        'persontype' => 'Analytisk og strategisk. God til at se mønstre, finde løsninger og kommunikere tekniske emner på en forståelig måde.',
        'sprog'      => ['Dansk (modersmål)', 'Engelsk (flydende)'],
        'interesser' => ['Fotografi', 'Teknologi & AI', 'E-commerce', 'Frivilligt arbejde'],
    ],
    'projects' => [],
    'cv' => [
        'jobs'            => [],
        'education'       => [],
        'recommendations' => [],
    ],
];

$wp_images = []; // Samler billeder der skal downloades manuelt

/* ---- BEHANDL POSTS ---- */

foreach ($all_posts as $post) {
    $cat_slugs    = array_map(fn($id) => get_category($id)->slug, wp_get_post_categories($post->ID));
    $body_text    = html_to_body($post->post_content);
    $excerpt_text = make_excerpt($post->post_excerpt ?: $post->post_content);
    $period       = format_period($post);

    $internal_cat = 'general';
    foreach ($cat_slugs as $slug) {
        if (isset($category_mapping[$slug])) {
            $internal_cat = $category_mapping[$slug];
            break;
        }
    }

    // Cover-billede
    $cover_local = '';
    $thumb_id    = get_post_thumbnail_id($post->ID);
    if ($thumb_id) {
        $cover_url   = wp_get_attachment_url($thumb_id);
        $ext         = pathinfo(parse_url($cover_url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $cover_local = 'assets/projects/' . $post->post_name . '.' . $ext;
        $wp_images[] = ['url' => $cover_url, 'save_as' => $cover_local];
    }

    $base = [
        'id'       => $post->post_name,
        'title'    => get_the_title($post->ID),
        'period'   => $period,
        'desc'     => $excerpt_text,
        'intro'    => $excerpt_text,
        'body'     => $body_text,
        'cover'    => $cover_local,
        'category' => $internal_cat,
        'skills'   => [],
        'links'    => [],
    ];

    switch ($internal_cat) {

        case 'work':
            $company = guess_company($base['title'] . ' ' . $body_text);
            $output['cv']['jobs'][] = array_merge($base, [
                'role'    => $base['title'],
                'company' => $company,
                'tag'     => 'Arbejde',
            ]);
            $output['projects'][] = array_merge($base, ['tag' => 'Arbejde']);
            break;

        case 'ai':
            $output['projects'][] = array_merge($base, ['tag' => 'AI']);
            break;

        case 'volunteer':
            $company = guess_company($base['title'] . ' ' . $body_text);
            $output['projects'][] = array_merge($base, ['tag' => 'Frivillig']);
            $output['cv']['jobs'][] = array_merge($base, [
                'role'    => $base['title'],
                'company' => $company ?: 'Frivillig',
                'tag'     => 'Frivillig',
            ]);
            break;

        case 'education':
            $institution = guess_institution($base['title'] . ' ' . $body_text);
            $output['cv']['education'][] = array_merge($base, [
                'title'       => $base['title'],
                'institution' => $institution,
            ]);
            break;

        case 'recommendation':
            $name = trim(preg_replace('/^anbefaling\s+fra\s+/i', '', $base['title']));
            $role = '';
            if (preg_match('/(?:hos|ved|i)\s+([A-ZÆØÅ][^,.\n]{3,40})/u', $body_text, $m)) {
                $role = trim($m[1]);
            }
            $output['cv']['recommendations'][] = [
                'id'   => $base['id'],
                'name' => $name,
                'role' => $role,
                'text' => $body_text,
            ];
            break;

        case 'about':
            if (strlen($body_text) > strlen($output['om']['bio'])) {
                $output['om']['bio'] = $body_text;
            }
            break;
    }
}

// Om Zacharias page
foreach ($all_pages as $page) {
    $slug = strtolower($page->post_name);
    if (strpos($slug, 'om-zacharias') !== false || $slug === 'om') {
        $bio = html_to_body($page->post_content);
        if (strlen($bio) > strlen($output['om']['bio'])) {
            $output['om']['bio'] = $bio;
        }
    }
}

$json = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (isset($_GET['download'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="content.json"');
    echo $json;
    exit;
}
?>
<!DOCTYPE html>
<html lang="da">
<head>
  <meta charset="UTF-8">
  <title>Migration v2 — Zacharias Polonius</title>
  <style>
    body{font-family:monospace;background:#0f0f0f;color:#e8e8e8;padding:40px;max-width:860px;line-height:1.6;}
    h2{color:#e05555;margin-bottom:8px;}
    h3{color:#418FFF;margin:28px 0 10px;font-size:12px;letter-spacing:0.12em;text-transform:uppercase;border-bottom:1px solid rgba(255,255,255,0.08);padding-bottom:8px;}
    .warning{background:rgba(224,85,85,0.1);border:1px solid #e05555;padding:16px;margin-bottom:20px;font-size:13px;}
    .success{background:rgba(65,164,71,0.1);border:1px solid #41A447;padding:14px;margin-bottom:16px;font-size:13px;}
    .stat{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.07);font-size:13px;}
    .stat-val{color:#418FFF;font-weight:bold;}
    .btn{display:inline-block;background:#418FFF;color:#fff;text-decoration:none;padding:12px 28px;font-size:13px;font-weight:bold;margin-top:20px;letter-spacing:0.05em;}
    table{width:100%;border-collapse:collapse;font-size:11px;margin-top:10px;}
    th{text-align:left;color:rgba(255,255,255,0.2);padding:6px 8px;border-bottom:1px solid rgba(255,255,255,0.08);letter-spacing:0.1em;font-size:10px;}
    td{padding:7px 8px;border-bottom:1px solid rgba(255,255,255,0.05);vertical-align:top;}
    .url{color:rgba(255,255,255,0.35);}
    .dest{color:#41A447;}
    .preview{background:#161616;border:1px solid rgba(255,255,255,0.08);padding:16px;margin-top:12px;font-size:10px;color:rgba(255,255,255,0.4);max-height:260px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;}
    .steps{font-size:13px;color:rgba(255,255,255,0.6);line-height:2.4;margin-top:20px;}
    .steps b{color:#418FFF;}
    .del{color:#e05555;font-weight:bold;}
  </style>
</head>
<body>
  <h2>⚠ Slet denne fil straks efter brug!</h2>
  <div class="warning">Denne fil har adgang til al din WordPress-data. Slet den umiddelbart efter du har downloadet content.json.</div>
  <div class="success">✓ Migration v2 — afsnit, perioder, institutioner og billedliste er forbedret.</div>

  <h3>Hvad blev fundet</h3>
  <?php
  $bio_len = strlen($output['om']['bio']);
  ?>
  <div class="stat"><span>Projekter</span><span class="stat-val"><?= count($output['projects']) ?></span></div>
  <div class="stat"><span>Ansættelser i CV</span><span class="stat-val"><?= count($output['cv']['jobs']) ?></span></div>
  <div class="stat"><span>Uddannelse</span><span class="stat-val"><?= count($output['cv']['education']) ?></span></div>
  <div class="stat"><span>Anbefalinger</span><span class="stat-val"><?= count($output['cv']['recommendations']) ?></span></div>
  <div class="stat"><span>Om mig bio</span><span class="stat-val"><?= $bio_len > 100 ? '✓ ' . $bio_len . ' tegn' : '⚠ Ikke fundet' ?></span></div>
  <div class="stat"><span>WordPress-billeder at downloade</span><span class="stat-val"><?= count($wp_images) ?></span></div>

  <a class="btn" href="?download=1">⬇ Download content.json</a>

  <?php if (!empty($wp_images)): ?>
  <h3>Billeder — download og upload manuelt til assets/projects/</h3>
  <table>
    <tr><th>WordPress URL</th><th>Gem som (på test.zpolonius.dk)</th></tr>
    <?php foreach ($wp_images as $img): ?>
    <tr>
      <td class="url"><?= htmlspecialchars($img['url']) ?></td>
      <td class="dest"><?= htmlspecialchars($img['save_as']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <h3>JSON preview</h3>
  <div class="preview"><?= htmlspecialchars(substr($json, 0, 3000)) ?>...</div>

  <div class="steps">
    <b>Næste trin:</b><br>
    1. Klik <b>Download content.json</b> ovenfor<br>
    2. Upload til <b>test.zpolonius.dk → data/content.json</b><br>
    3. Download billederne og upload til <b>assets/projects/</b><br>
    4. Gennemgå indholdet i <b>admin-panelet</b> og ret til<br>
    5. <span class="del">GÅ TIL FILE MANAGER OG SLET DENNE FIL NU!</span>
  </div>
</body>
</html>
