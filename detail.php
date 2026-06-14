<?php
// ============================================================================
// detail.php — Server-side rendering af detaljesider (cases, CV, indsigter,
// anbefalinger). Injicerer titel, meta, JSON-LD og hovedindhold i den rå HTML,
// så AI-crawlere (GPTBot, ClaudeBot, PerplexityBot m.fl.) ser fuldt indhold
// uden at køre JavaScript. Klienten (js/main.js) hydrerer bagefter og bevarer
// typewriter-effekten for menneskelige besøgende.
// ============================================================================

// Log evt. AI-bot crawl (fejler lydløst, påvirker ikke siden)
@include __DIR__ . '/api/log_ai_bot.php';

$id = isset($_GET['id']) ? preg_replace('/[^a-z0-9\-_]/i', '', $_GET['id']) : '';
$item = []; // tom = ikke fundet (falsy, men sikker for ?? på offsets)

$jsonFile = __DIR__ . '/data/content.json';
if ($id && file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true) ?: [];
    $all = [];
    foreach (($data['projects'] ?? []) as $x)            { $x['_cat'] = $x['category'] ?? 'ai';            $all[] = $x; }
    foreach (($data['cv']['jobs'] ?? []) as $x)          { $x['_cat'] = $x['category'] ?? 'work';          $all[] = $x; }
    foreach (($data['cv']['education'] ?? []) as $x)     { $x['_cat'] = $x['category'] ?? 'education';     $all[] = $x; }
    foreach (($data['cv']['recommendations'] ?? []) as $x){ $x['_cat'] = $x['category'] ?? 'recommendation'; $all[] = $x; }
    foreach (($data['articles'] ?? []) as $x)            { $x['_cat'] = $x['category'] ?? 'article';       $all[] = $x; }

    foreach ($all as $x) {
        if (($x['id'] ?? '') === $id && ($x['visible'] ?? true) !== false) { $item = $x; break; }
    }
}

if (!$item) { http_response_code(404); }

// ---- Afledte værdier ----
$baseUrl  = 'https://zpolonius.dk';
$h        = function ($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); };

$titleRaw = $item['title'] ?? $item['name'] ?? '';
$cat      = $item['_cat'] ?? 'ai';
$role     = $item['role'] ?? '';
// CV-job har hverken title/name — brug rollen som overskrift, så titel/H1 ikke er tomme
$title    = $titleRaw !== '' ? $titleRaw : $role;
$intro    = $item['intro'] ?? $item['desc'] ?? '';
$body     = $item['body'] ?? $item['text'] ?? '';
$cover    = $item['cover'] ?? $item['photo'] ?? '';
$coverPos = $item['cover_pos'] ?? $item['photo_pos'] ?? '';
$coverAlt = $item['cover_alt'] ?? $item['photo_alt'] ?? $title;
$tag      = $item['tag'] ?? $item['category'] ?? 'Case';
$company  = $item['company'] ?? $item['institution'] ?? '';
// Hvis rollen bruges som overskrift (CV-job), vis kun virksomheden på company-linjen
$companyLine = $titleRaw !== '' ? trim(($role ? $role . ' @ ' : '') . $company) : $company;
$period   = $item['period'] ?? '';
$date     = $item['date'] ?? '';
$skills   = $item['skills'] ?? [];
$bento    = $item['bento'] ?? [];

$rawDesc  = $item['meta_desc'] ?? $item['excerpt'] ?? $item['desc'] ?? $intro;
$metaDesc = trim(preg_replace('/\s+/', ' ', strip_tags($rawDesc)));
if (function_exists('mb_strlen') && mb_strlen($metaDesc) > 160) {
    $metaDesc = mb_substr($metaDesc, 0, 157) . '…';
}

$parentHref = ['article'=>'insights','ai'=>'projects','work'=>'cv','volunteer'=>'cv','education'=>'cv','recommendation'=>'recommendations'];
$parentName = ['article'=>'Indsigter','ai'=>'Projekter','work'=>'CV','volunteer'=>'Frivillig','education'=>'Uddannelse','recommendation'=>'Anbefalinger'];
$parent      = $parentHref[$cat] ?? 'projects';
$parentLabel = $parentName[$cat] ?? 'Projekter';

$canonical = $title ? ($baseUrl . '/' . $parent . '/' . $id) : ($baseUrl . '/');
$coverAbs  = $cover ? ($baseUrl . '/' . ltrim($cover, '/')) : ($baseUrl . '/assets/cover.webp');
$pageTitle = $title ? ($title . ' — Zacharias Polonius') : 'Siden blev ikke fundet — Zacharias Polonius';

// ---- JSON-LD (server-side) ----
$schemas = [];
if ($item) {
    $person = ['@type' => 'Person', 'name' => 'Zacharias Polonius', 'url' => $baseUrl . '/'];

    if ($cat === 'article') {
        $schemas[] = array_filter([
            '@context' => 'https://schema.org',
            '@type'    => 'BlogPosting',
            'headline' => $title,
            'description' => $metaDesc ?: null,
            'image'    => $cover ? $coverAbs : null,
            'datePublished' => $date ?: null,
            'author'   => $person,
            'publisher'=> $person,
            'inLanguage' => 'da',
            'url'      => $canonical,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
        ]);
    } elseif ($cat === 'recommendation') {
        $schemas[] = array_filter([
            '@context' => 'https://schema.org',
            '@type'    => 'Review',
            'reviewBody' => trim(strip_tags($intro ?: $body)),
            'author'   => ['@type' => 'Person', 'name' => $item['name'] ?? ''],
            'itemReviewed' => ['@type' => 'Person', 'name' => 'Zacharias Polonius', 'url' => $baseUrl . '/'],
        ]);
    } else {
        // Cases, job, uddannelse, frivilligt: CreativeWork forfattet af Zacharias
        $schemas[] = array_filter([
            '@context' => 'https://schema.org',
            '@type'    => 'CreativeWork',
            'name'     => $title,
            'headline' => $title,
            'description' => $metaDesc ?: null,
            'image'    => $cover ? $coverAbs : null,
            'keywords' => $skills ? implode(', ', $skills) : null,
            'author'   => $person,
            'inLanguage' => 'da',
            'url'      => $canonical,
        ]);
    }

    // BreadcrumbList for alle typer
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Hjem', 'item' => $baseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $parentLabel, 'item' => $baseUrl . '/' . $parent],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => $canonical],
        ],
    ];
}
?>
<!DOCTYPE html>
<html lang="da">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <base href="/">
  <title><?= $h($pageTitle) ?></title>
<?php if ($item): ?>
  <meta name="description" content="<?= $h($metaDesc) ?>">
  <link rel="canonical" href="<?= $h($canonical) ?>">

  <!-- Open Graph -->
  <meta property="og:type" content="<?= $cat === 'article' ? 'article' : 'website' ?>">
  <meta property="og:url" content="<?= $h($canonical) ?>">
  <meta property="og:title" content="<?= $h($title) ?>">
  <meta property="og:description" content="<?= $h($metaDesc) ?>">
  <meta property="og:image" content="<?= $h($coverAbs) ?>">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $h($title) ?>">
  <meta name="twitter:description" content="<?= $h($metaDesc) ?>">
  <meta name="twitter:image" content="<?= $h($coverAbs) ?>">

<?php foreach ($schemas as $s): ?>
  <script type="application/ld+json"><?= json_encode($s, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endforeach; ?>
<?php else: ?>
  <meta name="robots" content="noindex">
<?php endif; ?>

  <!-- Favicons -->
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
  <link rel="manifest" href="/site.webmanifest" />

  <link rel="stylesheet" href="css/style.css">
  <style>
    /* BREADCRUMB */
    .breadcrumb {
      padding: 24px 40px;
      font-size: 11px;
      letter-spacing: 0.05em;
      color: var(--text-faint);
      display: flex;
      gap: 12px;
      align-items: center;
      border-bottom: 0.5px solid var(--border);
      background: var(--bg);
    }
    .breadcrumb a { color: var(--text-muted); text-decoration: none; transition: color 0.2s; }
    .breadcrumb a:hover { color: var(--blue); }
    .breadcrumb-sep { color: var(--border-md); }
    .breadcrumb-current { color: var(--text-muted); font-weight: 500; }

    /* COVER */
    .cover {
      height: 480px;
      position: relative;
      overflow: hidden;
      background: var(--bg-card);
      border-bottom: 0.5px solid var(--border);
    }
    .cover-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.8s ease;
    }
    .cover:hover .cover-img { transform: scale(1.02); }
    .cover-gradient {
      position: absolute;
      bottom: 0; left: 0; width: 100%; height: 70%;
      background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    }
    .cover-fallback {
      position: absolute;
      top: 0; left: 0; width: 100%; height: 100%;
      background: var(--bg-card);
      display: none;
    }
    .cover-fallback.show { display: block; }
    .cover-fallback-pattern {
      width: 100%; height: 100%;
      opacity: 0.05;
      background-image: radial-gradient(var(--text) 0.5px, transparent 0.5px);
      background-size: 20px 20px;
    }
    .cover-content {
      position: absolute;
      bottom: 0; left: 0; width: 100%;
      padding: 60px 40px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      z-index: 10;
    }
    .cover-tag {
      font-size: 11px; letter-spacing: 0.15em; text-transform: uppercase;
      color: var(--blue); margin-bottom: 12px; font-weight: 600;
    }
    .cover-title {
      font-size: 48px; font-weight: 700; color: #fff;
      letter-spacing: -0.04em; line-height: 1.1; margin-bottom: 8px;
    }
    .cover-company { font-size: 18px; color: rgba(255,255,255,0.7); }
    .cover-right { text-align: right; }
    .cover-period { font-size: 14px; color: rgba(255,255,255,0.6); margin-bottom: 4px; }
    .cover-photo-credit { font-size: 10px; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.05em; }

    /* NEW DETAIL GRID LAYOUT */
    .detail-grid {
      display: grid;
      grid-template-columns: 200px 1fr;
      gap: 80px;
      max-width: 1100px;
      margin: 0 auto;
      padding: 80px 40px;
    }

    .meta-sidebar {
      position: sticky;
      top: 120px;
      height: fit-content;
      display: flex;
      flex-direction: column;
      gap: 32px;
    }

    .intro-text {
      font-size: 28px;
      line-height: 1.5;
      color: var(--text);
      font-weight: 400;
      letter-spacing: -0.01em;
      margin-bottom: 48px;
    }

    .content-main {
      font-size: 18px;
      line-height: 1.8;
      color: var(--text-muted);
      max-width: 720px; /* Perfekt læsebredde */
    }
    .content-main h2 { font-size: 32px; color: var(--text); margin: 48px 0 24px; letter-spacing: -0.02em; }
    .content-main h3 { font-size: 26px; color: var(--text); margin: 32px 0 16px; }
    .content-main p { margin-bottom: 24px; }
    .content-main ul, .content-main ol { margin-bottom: 32px; padding-left: 20px; }
    .content-main li { margin-bottom: 12px; }
    .content-main strong { color: var(--text); font-weight: 600; }

    /* SIDEBAR (Now centered footer info) */
    .sidebar {
      margin-top: 40px;
      padding-top: 40px;
      border-top: 0.5px solid var(--border);
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
    }
    .sidebar-block { margin-bottom: 0; }
    .sidebar-title {
      font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em;
      color: var(--text-faint); margin-bottom: 20px;
      padding-bottom: 12px; border-bottom: 0.5px solid var(--border);
    }
    .skill-pills { display: flex; flex-wrap: wrap; gap: 8px; }
    .skill-pill {
      font-size: 11px; padding: 6px 12px;
      background: var(--bg-card); border: 0.5px solid var(--border-md);
      border-radius: 100px; color: var(--text-muted);
    }

    /* CONVERSION CTA */
    .detail-cta {
      margin: 60px 40px 80px;
      background: var(--bg-card);
      border: 0.5px solid var(--border-md);
      border-radius: 24px;
      padding: 64px 40px;
      text-align: center;
      box-shadow: 0 15px 50px rgba(0,0,0,0.3);
      position: relative;
      overflow: hidden;
    }
    .detail-cta::before {
      content: '';
      position: absolute;
      top: 0; left: 0; width: 100%; height: 100%;
      background: radial-gradient(circle at top right, rgba(65,143,255,0.08), transparent);
      pointer-events: none;
    }
    .detail-cta-label {
      font-size: 11px; letter-spacing: 0.15em; text-transform: uppercase;
      color: var(--blue); margin-bottom: 16px; font-weight: 600;
    }
    .detail-cta-title {
      font-size: 36px; font-weight: 700; color: var(--text);
      letter-spacing: -0.04em; margin-bottom: 16px; line-height: 1.1;
    }
    .detail-cta-desc {
      font-size: 16px; color: var(--text-muted); line-height: 1.7;
      max-width: 520px; margin: 0 auto 32px;
    }

    /* ENTRY NAV */
    .entry-nav {
      display: grid;
      grid-template-columns: 1fr 1fr;
      border-top: 0.5px solid var(--border);
      border-bottom: 0.5px solid var(--border);
    }
    .entry-nav-item {
      padding: 40px;
      text-decoration: none;
      display: flex;
      flex-direction: column;
      gap: 8px;
      transition: background 0.2s;
    }
    .entry-nav-item:hover { background: rgba(65,143,255,0.03); }
    .entry-nav-item:first-child { border-right: 0.5px solid var(--border); }
    .entry-nav-dir {
      font-size: 10px;
      color: var(--text-ghost);
      letter-spacing: 0.1em;
      text-transform: uppercase;
    }
    .entry-nav-title {
      font-size: 15px;
      color: var(--text-muted);
      font-weight: 500;
      transition: color 0.2s;
    }
    .entry-nav-item:hover .entry-nav-title { color: var(--blue); }

    /* MOBILE OPTIMIZATIONS */
    @media (max-width: 768px) {
      .breadcrumb { padding: 16px 20px; }
      .cover { height: 320px; }
      .cover-content { padding: 32px 20px; }
      .cover-title { font-size: 32px; }
      .cover-right { display: none; }

      .detail-grid {
        grid-template-columns: 1fr;
        padding: 40px 20px;
        gap: 40px;
      }
      .meta-sidebar {
        position: static;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 20px;
        padding-bottom: 32px;
        border-bottom: 0.5px solid var(--border);
      }
      .intro-text { font-size: 18px; margin-bottom: 32px; }
      .sidebar { grid-template-columns: 1fr; }

      .detail-cta { margin: 40px 20px 60px; padding: 48px 24px; border-radius: 20px; }
      .detail-cta-title { font-size: 28px; }

      .entry-nav { grid-template-columns: 1fr; }
      .entry-nav-item:first-child { border-right: none; border-bottom: 0.5px solid var(--border); }
      .entry-nav-item { padding: 32px 20px; }
    }

    /* ---- NOT FOUND ---- */
    .not-found { padding: 80px 40px; text-align: center; display: none; }
    .not-found.show { display: block; }
    .not-found h2 { font-size: 24px; color: var(--text); margin-bottom: 12px; }
  </style>
</head>
<body>
  <header id="global-header"></header>
  <div id="global-menu"></div>
  <div id="global-contact"></div>
  <nav id="global-bottom-nav"></nav>
  <div id="global-floating-cta"></div>

  <main>
  <!-- BREADCRUMB -->
  <div class="breadcrumb">
    <a href="/">Hjem</a>
    <span class="breadcrumb-sep">/</span>
    <a href="<?= $h($parent) ?>" id="bcParent"><?= $h($parentLabel) ?></a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current" id="bcCurrent"><?= $item ? $h($title) : 'Indlæser…' ?></span>
  </div>

  <div id="mainContent"<?= $item ? '' : ' style="display:none"' ?>>
    <!-- COVER -->
    <div class="cover" id="cover">
      <img class="cover-img" id="coverImg" src="<?= $h($cover) ?>" alt="<?= $h($coverAlt) ?>"<?= $coverPos ? ' style="object-position:' . $h($coverPos) . '"' : '' ?> onerror="this.style.display='none'; document.getElementById('coverFallback').classList.add('show');">
      <div class="cover-fallback<?= $cover ? '' : ' show' ?>" id="coverFallback"><div class="cover-fallback-pattern"></div></div>
      <div class="cover-gradient"></div>
      <div class="cover-content">
        <div>
          <div class="cover-tag" id="coverTag"><?= $h($tag) ?></div>
          <h1 class="cover-title" id="coverTitle"><?= $h($title) ?></h1>
          <div class="cover-company" id="coverCompany"><?= $h($companyLine) ?></div>
        </div>
        <div class="cover-right">
          <div class="cover-period" id="coverPeriod"><?= $h($period) ?></div>
          <div class="cover-photo-credit" id="coverCredit"></div>
        </div>
      </div>
    </div>

    <div class="detail-grid">
      <!-- LEFT SIDEBAR -->
      <aside class="meta-sidebar" id="metaCol"><?php if ($skills): ?><div class="meta-item" style="margin-top:16px;"><div class="meta-item-label">Kompetencer</div><div class="skill-pills"><?php foreach ($skills as $s): ?><span class="skill-pill"><?= $h($s) ?></span><?php endforeach; ?></div></div><?php endif; ?></aside>

      <!-- MAIN CONTENT -->
      <main class="detail-main">
        <div id="introContainer">
          <div class="intro-text" id="introText"><?= $intro /* betroet CMS-indhold */ ?></div>
        </div>

        <div class="bento-grid" id="caseBentoGrid"<?= $bento ? '' : ' hidden' ?>><?php foreach ($bento as $i => $c): if (!($c['label'] ?? '') && !($c['value'] ?? '') && !($c['sub'] ?? '') && !($c['desc'] ?? '')) continue; ?><div class="bento-cell<?= (!empty($c['accent']) && $c['accent'] !== 'none') ? ' accent-' . $h($c['accent']) : '' ?>" data-i="<?= $i ?>" tabindex="0" role="button" aria-expanded="false"><div class="cell-label"><?= $h($c['label'] ?? '') ?></div><h3 class="cell-title"><?= $h($c['value'] ?? '') ?></h3><div class="cell-sub"><?= $h($c['sub'] ?? '') ?></div><div class="cell-desc"><?= $h($c['desc'] ?? '') ?></div><span class="cell-arrow">Læs mere →</span></div><?php endforeach; ?></div>

        <div class="content-main" id="contentMain"><?= $body ?: '<p>Ingen yderligere beskrivelse tilgængelig.</p>' /* betroet CMS-indhold */ ?></div>

        <div class="sidebar" id="contentSidebar"></div>
      </main>
    </div>

    <!-- CONVERSION CTA -->
    <div class="detail-cta fade-up">
      <div class="detail-cta-inner">
        <div class="detail-cta-label">Klar til næste skridt?</div>
        <h2 class="detail-cta-title">Lad os se på dine tal</h2>
        <p class="detail-cta-desc">Er du nysgerrig på, hvordan lignende optimeringer kan hjælpe din forretning? Lad os tage en uforpligtende snak om dit checkout-flow.</p>
        <button class="btn-primary" data-contact>Book et review →</button>
      </div>
    </div>

    <!-- ENTRY NAV -->
    <nav class="entry-nav" id="entryNav"></nav>
  </div>

  <!-- NOT FOUND -->
  <div class="not-found<?= $item ? '' : ' show' ?>" id="notFound">
    <h2>Siden blev ikke fundet</h2>
    <p>Det ser ud til, at denne case eller artikel ikke eksisterer længere.</p>
    <a href="/" class="btn-primary" style="margin-top:24px; display:inline-block;">Gå til forsiden</a>
  </div>

  </main>
  <div id="global-cta-bar"></div>
  <footer id="global-footer" class="footer"></footer>

  <script src="js/main.js?v=1.0.7"></script>
  <script>
    const urlParams = new URLSearchParams(window.location.search);
    let itemId = urlParams.get('id');

    // Understøt rene URL'er: /projekter/slug, /artikler/slug, /cv/slug, /anbefalinger/slug
    if (!itemId) {
      const pathParts = window.location.pathname.replace(/\/$/, '').split('/').filter(Boolean);
      if (pathParts.length >= 2) itemId = pathParts[pathParts.length - 1];
    }

    if (!itemId) {
      show404();
    } else {
      loadContentData().then(data => {
        if (!data) return;
        const allItems = [
          ...(data.projects || []),
          ...(data.cv?.jobs || []),
          ...(data.cv?.education || []),
          ...(data.cv?.recommendations || []),
          ...(data.articles || [])
        ];
        const item = allItems.find(x => x.id === itemId);
        if (!item) { show404(); return; }
        renderDetail(item, allItems);
      });
    }

    function getDetailUrl(item) {
      const cat = item.category || '';
      if (cat === 'article') return `insights/${item.id}`;
      if (cat === 'work' || cat === 'education' || cat === 'volunteer') return `cv/${item.id}`;
      if (cat === 'recommendation') return `recommendations/${item.id}`;
      return `projects/${item.id}`;
    }

    function show404() {
      document.getElementById('mainContent').style.display = 'none';
      document.getElementById('notFound').classList.add('show');
    }

    function esc(str) {
      if (!str) return '';
      const div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    }

    function renderDetail(item, allItems) {
      const parentHref = {
        article:'insights',
        ai:'projects',
        work:'cv',
        volunteer:'cv',
        education:'cv',
        recommendation:'recommendations'
      };
      const parentName = {
        article:'Indsigter',
        ai:'Projekter',
        work:'CV',
        volunteer:'Frivillig',
        education:'Uddannelse',
        recommendation:'Anbefalinger'
      };

      // Breadcrumb
      const cat = item.category || 'ai';
      document.getElementById('bcParent').href = parentHref[cat] || 'projects';
      document.getElementById('bcParent').textContent = parentName[cat] || 'Projekter';
      document.getElementById('bcCurrent').textContent = item.title || item.name || item.role || '';

      // CV-job har hverken title/name — brug rollen som overskrift
      const titleRaw = item.title || item.name || '';
      const displayTitle = titleRaw || item.role || '';
      const companyLine = titleRaw
        ? ((item.role ? item.role + ' @ ' : '') + (item.company || item.institution || ''))
        : (item.company || item.institution || '');

      document.getElementById('coverTag').textContent = item.tag || item.category || 'Case';
      document.getElementById('coverCompany').textContent = companyLine;
      if (item.period) document.getElementById('coverPeriod').textContent = item.period;

      // SEO Updates
      const pageTitle = displayTitle + " — Zacharias Polonius";
      document.title = pageTitle;
      const metaDesc = item.meta_desc || item.excerpt || item.desc || "";
      if (metaDesc) {
        let metaTag = document.querySelector('meta[name="description"]');
        if (!metaTag) {
          metaTag = document.createElement('meta');
          metaTag.name = "description";
          document.head.appendChild(metaTag);
        }
        metaTag.content = metaDesc.substring(0, 160);
      }

      const img = document.getElementById('coverImg');
      const fallback = document.getElementById('coverFallback');

      if (item.cover || item.photo) {
        const imgPath = item.cover || item.photo;
        img.src = imgPath;
        img.style.display = 'block';
        fallback.classList.remove('show');
        if (item.cover_pos || item.photo_pos) img.style.objectPosition = item.cover_pos || item.photo_pos;
      } else {
        img.style.display = 'none';
        fallback.classList.add('show');
      }

      // Intro with Typewriter
      const introEl = document.getElementById('introText');
      const fullIntro = item.intro || item.desc || "";
      introEl.innerHTML = ""; // Reset

      let introIdx = 0;
      function typeIntro() {
        if (introIdx < fullIntro.length) {
          // Vi tjekker om vi rammer et HTML tag (<p>, <strong> osv)
          if (fullIntro[introIdx] === '<') {
            const endTag = fullIntro.indexOf('>', introIdx);
            if (endTag !== -1) {
              introEl.innerHTML += fullIntro.substring(introIdx, endTag + 1);
              introIdx = endTag + 1;
              typeIntro(); // Fortsæt med det samme efter tagget
              return;
            }
          }
          introEl.innerHTML += fullIntro.charAt(introIdx);
          introIdx++;
          setTimeout(typeIntro, 15); // Hurtigere fart til den lange tekst
        }
      }

      // Title typewriter (allerede implementeret tidligere)
      const titleEl = document.getElementById('coverTitle');
      const fullTitle = displayTitle;
      titleEl.textContent = "";

      let charIdx = 0;
      function typeTitle() {
        if (charIdx < fullTitle.length) {
          titleEl.textContent += fullTitle.charAt(charIdx);
          charIdx++;
          setTimeout(typeTitle, 40);
        } else {
          // Når titlen er færdig, start introen!
          setTimeout(typeIntro, 200);
        }
      }
      setTimeout(typeTitle, 300);

      // Case Bento
      const bentoEl = document.getElementById('caseBentoGrid');
      const cells = (item.bento || []).filter(c => c && (c.label || c.value || c.sub || c.desc));
      if (cells.length > 0) {
        bentoEl.innerHTML = cells.map((c, i) => `
          <div class="bento-cell ${c.accent && c.accent !== 'none' ? 'accent-' + c.accent : ''}" data-i="${i}" tabindex="0" role="button" aria-expanded="false">
            <div class="cell-label">${esc(c.label)}</div>
            <h3 class="cell-title">${esc(c.value)}</h3>
            <div class="cell-sub">${esc(c.sub)}</div>
            <div class="cell-desc">${esc(c.desc)}</div>
            <span class="cell-arrow">Læs mere →</span>
          </div>`).join('');
        bentoEl.hidden = false;
        bentoEl.querySelectorAll('.bento-cell').forEach(el => {
          const toggle = () => {
            const open = el.classList.toggle('expanded');
            el.setAttribute('aria-expanded', open ? 'true' : 'false');
          };
          el.addEventListener('click', toggle);
          el.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
          });
        });
      } else {
        bentoEl.hidden = true;
      }

      // Meta
      let metaHtml = '';
      if (item.category === 'article') {
        const rTime = item.reading_time || (item.body ? window.getReadingTime(item.body) : 0);
        if (rTime) metaHtml += `<div class="meta-item"><div class="meta-item-label">Læsetid</div><div class="meta-item-val">${rTime} minutter</div></div>`;
      }
      if (item.category) metaHtml += `<div class="meta-item"><div class="meta-item-label">Kategori</div><div class="meta-item-val">${esc(item.category)}</div></div>`;
      if (item.client) metaHtml += `<div class="meta-item"><div class="meta-item-label">Klient</div><div class="meta-item-val">${esc(item.client)}</div></div>`;
      // Links & Files
      let linksHtml = '';

      // Check for direct URL (e.g. review-manager)
      if (item.url) {
        linksHtml += `<a href="${item.url}" target="_blank" class="btn-outline" style="font-size:12px; padding:8px 14px; margin-bottom:8px; display:inline-block;">Gå til Projekt ↗</a>`;
      }

      // Check for PDF/File
      if (item.file) {
        const isPdf = item.file.toLowerCase().endsWith('.pdf');
        const label = isPdf ? 'Se Dokument (PDF) 📄' : 'Se Ressource ↗';
        linksHtml += `<a href="${item.file}" target="_blank" class="btn-outline" style="font-size:12px; padding:8px 14px; margin-bottom:8px; display:inline-block;">${label}</a>`;
      }

      // Check for links array
      if (item.links && item.links.length > 0) {
        item.links.forEach(l => {
          linksHtml += `<a href="${l.url}" target="_blank" class="btn-outline" style="font-size:12px; padding:8px 14px; margin-bottom:8px; display:inline-block;">${esc(l.label)} ↗</a>`;
        });
      }

      if (linksHtml) {
        metaHtml += `<div class="meta-item"><div class="meta-item-label">Ressourcer</div><div class="meta-links">${linksHtml}</div></div>`;
      }

      // Skills in Sidebar
      if (item.skills && item.skills.length > 0) {
        metaHtml += `<div class="meta-item" style="margin-top:16px;"><div class="meta-item-label">Kompetencer</div><div class="skill-pills">`;
        item.skills.forEach(s => { metaHtml += `<span class="skill-pill">${esc(s)}</span>`; });
        metaHtml += `</div></div>`;
      }

      document.getElementById('metaCol').innerHTML = metaHtml;

      // Body Content
      document.getElementById('contentMain').innerHTML = item.body || item.text || '<p>Ingen yderligere beskrivelse tilgængelig.</p>';

      // Clear footer sidebar as it's now in the side-sidebar
      document.getElementById('contentSidebar').innerHTML = '';

      // Entry Nav (Prev/Next)
      const currentCatItems = allItems.filter(x => x.category === item.category && x.visible !== false);
      const idx = currentCatItems.findIndex(x => x.id === item.id);
      let navHtml = '';

      if (idx > 0) {
        const prev = currentCatItems[idx - 1];
        navHtml += `<a href="${getDetailUrl(prev)}" class="entry-nav-item">
          <div class="entry-nav-dir">← Forrige</div>
          <div class="entry-nav-title">${esc(prev.title || prev.name)}</div>
        </a>`;
      } else { navHtml += '<div></div>'; }

      if (idx < currentCatItems.length - 1) {
        const next = currentCatItems[idx + 1];
        navHtml += `<a href="${getDetailUrl(next)}" class="entry-nav-item" style="text-align:right;">
          <div class="entry-nav-dir">Næste →</div>
          <div class="entry-nav-title">${esc(next.title || next.name)}</div>
        </a>`;
      }
      document.getElementById('entryNav').innerHTML = navHtml;
    }
  </script>
</body>
</html>
