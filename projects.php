<?php
// Log evt. AI-bot crawl (fejler lydløst)
@include __DIR__ . '/api/log_ai_bot.php';

$h = function ($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); };
$plainText = function ($s) { return trim(preg_replace('/\s+/', ' ', strip_tags($s ?? ''))); };
$data = json_decode(@file_get_contents(__DIR__ . '/data/content.json'), true) ?: [];
$projects = array_values(array_filter($data['projects'] ?? [], function ($p) { return ($p['visible'] ?? true) !== false; }));
$catLabels = ['ai'=>'AI','checkout'=>'Checkout','konvertering'=>'Konvertering','work'=>'Arbejde','volunteer'=>'Frivillig'];
$catClass  = ['ai'=>'tag-ai','checkout'=>'tag-ai','konvertering'=>'tag-ai','work'=>'tag-work','volunteer'=>'tag-vol'];
?>
<!DOCTYPE html>
<html lang="da">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <base href="/">
  <title>Projekter & Cases — Zacharias Polonius</title>
  <meta name="description" content="Se mine udvalgte business cases inden for AI, checkout-optimering og teknisk rådgivning. Resultatorienterede løsninger med fokus på ROI.">
  
  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://zpolonius.dk/projects">
  <meta property="og:title" content="Business Cases & Projekter — Zacharias Polonius">
  <meta property="og:description" content="Se hvordan jeg skaber forretningsværdi gennem teknologi.">
  <meta property="og:image" content="https://zpolonius.dk/assets/og-projects.jpg">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="https://zpolonius.dk/projects">
  <meta property="twitter:title" content="Business Cases & Projekter — Zacharias Polonius">
  <meta property="twitter:description" content="Se hvordan jeg skaber forretningsværdi gennem teknologi.">
  <meta property="twitter:image" content="https://zpolonius.dk/assets/og-projects.jpg">
  <link rel="canonical" href="https://zpolonius.dk/projects">

  <!-- Structured Data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "Zacharias Polonius",
    "url": "https://zpolonius.dk/",
    "jobTitle": "Technical Account Manager",
    "worksFor": { "@type": "Organization", "name": "Bring", "url": "https://www.bring.dk/" },
    "sameAs": [
      "https://www.linkedin.com/in/zpolonius/",
      "https://github.com/zpolonius",
      "https://www.instagram.com/zackp91/"
    ]
  }
  </script>
<?php
$collection = [
  '@context' => 'https://schema.org',
  '@type'    => 'CollectionPage',
  'name'     => 'Projekter & Cases — Zacharias Polonius',
  'url'      => 'https://zpolonius.dk/projects',
  'mainEntity' => ['@type' => 'ItemList', 'itemListElement' => []],
];
$pos = 1;
foreach ($projects as $p) {
  $collection['mainEntity']['itemListElement'][] = [
    '@type' => 'ListItem', 'position' => $pos++,
    'url'   => 'https://zpolonius.dk/projects/' . ($p['id'] ?? ''),
    'name'  => $p['title'] ?? '',
  ];
}
?>
  <script type="application/ld+json"><?= json_encode($collection, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .page-hero {
      padding: 52px 40px 44px;
      border-bottom: 0.5px solid var(--border);
      display: grid;
      grid-template-columns: 1fr auto;
      align-items: flex-end;
      gap: 32px;
    }
    .page-hero-label {
      font-size: 10px; letter-spacing: 0.15em; text-transform: uppercase;
      color: var(--blue); margin-bottom: 14px;
    }
    .page-hero-title {
      font-size: 44px; font-weight: 700; letter-spacing: -0.04em;
      color: var(--text); line-height: 1.0; margin-bottom: 14px;
    }
    .page-hero-desc {
      font-size: 14px; color: var(--text-muted); line-height: 1.7; max-width: 480px;
    }
    .page-hero-stat { text-align: right; }
    .page-hero-stat-num { font-size: 36px; font-weight: 700; color: var(--blue); letter-spacing: -0.03em; }
    .page-hero-stat-lbl { font-size: 11px; color: var(--text-faint); letter-spacing: 0.08em; text-transform: uppercase; margin-top: 2px; }

    /* FILTER BAR */
    .filter-bar {
      display: flex; align-items: center; gap: 8px;
      padding: 14px 40px; border-bottom: 0.5px solid var(--border);
      overflow-x: auto; -webkit-overflow-scrolling: touch;
      position: relative; z-index: 10;
      scrollbar-width: none;
    }
    .filter-bar::-webkit-scrollbar { display: none; }
    .filter-btn {
      font-size: 11px; letter-spacing: 0.06em; text-transform: uppercase;
      padding: 10px 20px; border: 0.5px solid var(--border);
      color: var(--text-faint); background: transparent; cursor: pointer;
      font-family: var(--font); transition: all 0.2s; white-space: nowrap;
      flex-shrink: 0; min-height: 40px; display: flex; align-items: center;
      border-radius: 100px;
    }
    .filter-btn:hover { border-color: var(--border-md); color: var(--text-muted); }
    .filter-btn.active { border-color: var(--blue); color: var(--blue); background: rgba(65,143,255,0.06); }

    /* SPECIALER STRIP */
    .specialer-strip {
      display: grid; grid-template-columns: repeat(3, 1fr);
      border-bottom: 0.5px solid var(--border);
    }
    .special-cell {
      padding: 22px 28px; border-right: 0.5px solid var(--border);
      display: flex; align-items: flex-start; gap: 14px;
    }
    .special-cell:nth-child(3n) { border-right: none; }
    .special-cell:nth-child(n+4) { border-top: 0.5px solid var(--border); }
    .special-icon-wrap {
      width: 32px; height: 32px; display: flex; align-items: center;
      justify-content: center; border: 0.5px solid var(--border-blue);
      font-size: 14px; flex-shrink: 0; color: var(--blue);
    }
    .special-icon-wrap.green { border-color: var(--border-green); color: var(--green); }
    .special-cell-title { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
    .special-cell-desc  { font-size: 12px; color: var(--text-faint); line-height: 1.6; }

    /* PROJECT GRID */
    .proj-grid { display: grid; grid-template-columns: repeat(3, 1fr); }
    .proj-card {
      padding: 0; border-right: 0.5px solid var(--border);
      border-bottom: 0.5px solid var(--border);
      display: flex; flex-direction: column; position: relative; transition: background 0.2s;
    }
    .proj-card-img-wrap {
      width: 100%; height: 180px; overflow: hidden; background: var(--bg-card);
      border-bottom: 0.5px solid var(--border);
    }
    .proj-card-img {
      width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;
    }
    .proj-card:hover .proj-card-img { transform: scale(1.05); }
    .proj-card-body { padding: 24px 28px; }
    .proj-card:hover { background: rgba(65,143,255,0.03); }
    .proj-card:hover .proj-arrow { opacity: 1; }
    .proj-card:nth-child(3n) { border-right: none; }
    .proj-card.hidden { display: none; }
    .proj-tag { margin-bottom: 14px; }
    .proj-title { font-size: 16px; font-weight: 600; color: var(--text); letter-spacing: -0.02em; margin-bottom: 6px; line-height: 1.3; }
    .proj-period { font-size: 10px; color: var(--blue); letter-spacing: 0.08em; margin-bottom: 8px; }
    .proj-desc { font-size: 14px; color: var(--text-muted); line-height: 1.65; }
    .proj-skills { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 14px; }
    .proj-skill { font-size: 9px; letter-spacing: 0.06em; padding: 3px 7px; border: 0.5px solid var(--border); color: var(--text-faint); }
    .proj-arrow { position: absolute; bottom: 16px; right: 20px; font-size: 11px; color: var(--blue); }

    .no-results {
      grid-column: 1/-1; padding: 60px 40px; text-align: center;
      color: var(--text-faint); font-size: 13px; display: none;
    }
    .no-results.show { display: block; }

    /* ---- BENTO GRID ---- */
    /* CSS flyttet til style.css for konsistens */

    /* ---- BENTO DETAIL ---- */
    .detail-panel { display: none; border-bottom: 0.5px solid var(--border); background: var(--bg-card); }
    .detail-panel.active { display: block; }
    .detail-inner { padding: 36px 40px; display: grid; grid-template-columns: 1fr 320px; gap: 40px; }
    .detail-tag { display: inline-block; font-size: 9px; letter-spacing: 0.15em; text-transform: uppercase; padding: 3px 8px; margin-bottom: 14px; border: 0.5px solid var(--border-blue); color: var(--blue); }
    .detail-title { font-size: 26px; font-weight: 700; color: var(--text); margin-bottom: 12px; line-height: 1.1; }
    .detail-desc { font-size: 13px; color: var(--text-muted); line-height: 1.75; margin-bottom: 22px; }
    .detail-close { background: transparent; color: var(--text-muted); border: 0.5px solid var(--border-md); padding: 9px 18px; font-size: 12px; transition: all 0.2s; border-radius: 4px; cursor: pointer; }
    .detail-close:hover { color: var(--text); border-color: var(--blue); color: var(--blue); }
    .detail-meta { display: flex; flex-direction: column; }
    .detail-row { display: flex; gap: 12px; padding: 12px 0; border-bottom: 0.5px solid var(--border); align-items: baseline; }
    .detail-key { font-size: 10px; text-transform: uppercase; color: var(--text-faint); min-width: 80px; }
    .detail-val { font-size: 13px; color: var(--text-muted); }

    @media (max-width: 768px) {
      /* PAGE HERO — compact on mobile */
      .page-hero {
        grid-template-columns: 1fr;
        padding: 32px 20px 28px;
        gap: 20px;
      }
      .page-hero-title { font-size: 26px; }
      .page-hero-stat { text-align: left; }
      .page-hero-stat-num { font-size: 28px; }

      /* FILTER BAR — scrollable */
      .filter-bar { padding: 12px 20px; gap: 6px; }
      .filter-btn { padding: 6px 14px; font-size: 10px; }

      /* SPECIALER STRIP — 1 column stacked */
      .specialer-strip { grid-template-columns: 1fr; }
      .special-cell { padding: 18px 20px; border-right: none; }
      .special-cell:nth-child(n) { border-top: 0.5px solid var(--border); }
      .special-cell:first-child { border-top: none; }

      /* PROJECT GRID — 1 column, full width */
      .proj-grid { grid-template-columns: 1fr; }
      .proj-card { border-right: none !important; }
      .proj-card:nth-child(3n) { border-right: none; }
      .proj-card-body { padding: 20px; }
      .proj-card-img-wrap { height: 200px; }
      .proj-title { font-size: 17px; }
    }
  </style>
</head>
<body>
  <!-- SHARED WIDGETS -->
  <header id="global-header"></header>
  <div id="global-menu"></div>
  <div id="global-contact"></div>
  <nav id="global-bottom-nav"></nav>
  <div id="global-floating-cta"></div>

  <!-- PAGE HERO -->
  <main>
  <div class="page-hero">
    <div class="fade-up fade-up-1">
      <div class="page-hero-label">Projekter & Cases</div>
      <h1 class="page-hero-title">Du har tilføjet<br>noget til verden,<br>som ikke var der før.</h1>
      <p class="page-hero-desc">Fra AI-drevne apps til checkout-optimering og teknisk rådgivning — her er et udvalg af mine projekter og cases.</p>
    </div>
    <div class="page-hero-stat fade-up fade-up-2">
      <div class="page-hero-stat-num" id="projCount">—</div>
      <div class="page-hero-stat-lbl">Projekter</div>
    </div>
  </div>

  <!-- SPECIALER STRIP -->
  <div class="section-header">
    <h2 class="section-title">Mine specialer</h2>
  </div>
  <div class="specialer-strip" id="specialerStrip">
    <!-- Rendered by JS -->
  </div>

  <!-- FILTER + PROJEKTER -->
  <div class="section-header" style="margin-top:0;">
    <h2 class="section-title">Alle projekter</h2>
  </div>
  <div class="filter-bar" id="filterBar">
    <button class="filter-btn active" onclick="filterProjects('all', this)">Alle</button>
    <!-- Kategori-knapper tilføjes dynamisk -->
  </div>

  <div class="proj-grid" id="projGrid">
<?php foreach ($projects as $p):
    $cat = $p['category'] ?? '';
    $cover = $p['cover'] ?? '';
    $sk = array_slice($p['skills'] ?? [], 0, 4); ?>
    <a class="proj-card" href="projects/<?= $h($p['id'] ?? '') ?>" data-cat="<?= $h($cat) ?>">
      <?php if ($cover): ?><div class="proj-card-img-wrap"><img src="<?= $h($cover) ?>" alt="<?= $h($p['cover_alt'] ?? $p['title'] ?? '') ?>" class="proj-card-img" style="object-position: <?= $h($p['cover_pos'] ?? 'center center') ?>" loading="lazy" onerror="this.parentElement.style.display='none'"></div><?php endif; ?>
      <div class="proj-card-body">
        <div class="tag <?= $h($catClass[$cat] ?? '') ?> proj-tag"><?= $h($catLabels[$cat] ?? ($p['tag'] ?? $cat)) ?></div>
        <div class="proj-title"><?= $h($p['title'] ?? '') ?></div>
        <?php if (!empty($p['period'])): ?><div class="proj-period"><?= $h($p['period']) ?></div><?php endif; ?>
        <div class="proj-desc"><?= $h($plainText($p['desc'] ?? '')) ?></div>
        <?php if ($sk): ?><div class="proj-skills"><?php foreach ($sk as $s): ?><span class="proj-skill"><?= $h($plainText($s)) ?></span><?php endforeach; ?></div><?php endif; ?>
        <span class="proj-arrow">Læs mere →</span>
      </div>
    </a>
<?php endforeach; ?>
    <div class="no-results" id="noResults">Ingen projekter i denne kategori endnu.</div>
  </div>

  <!-- GLOBAL BENTO -->
  <div id="global-bento"></div>
  <div id="global-detail-panel"></div>

  </main>

  <!-- CENTRAL CTA BAR -->
  <div id="global-cta-bar"></div>

  <!-- SHARED FOOTER -->
  <footer id="global-footer" class="footer"></footer>


  <script src="js/main.js?v=1.0.7"></script>
  <script>
    function esc(str) {
      if (!str) return '';
      const div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    }
    
    function stripHtml(html) {
      if (!html) return '';
      const div = document.createElement('div');
      div.innerHTML = html;
      return div.textContent || div.innerText || "";
    }

    const catLabels = { ai:'AI', checkout:'Checkout', konvertering:'Konvertering', work:'Arbejde', volunteer:'Frivillig' };
    const catClass  = { ai:'tag-ai', checkout:'tag-ai', konvertering:'tag-ai', work:'tag-work', volunteer:'tag-vol' };

    loadContentData().then(data => {
      if (!data) return;
      const visibleProjects = (data.projects||[]).filter(p => p.visible !== false);

        // Count
        document.getElementById('projCount').textContent = visibleProjects.length;

        // Specialer
        const strip = document.getElementById('specialerStrip');
        (data.specialer||[]).forEach(s => {
          if (s.visible === false) return;
          strip.innerHTML += `
            <div class="special-cell">
              <div class="special-icon-wrap ${s.color==='green'?'green':''}">${esc(s.icon||'◆')}</div>
              <div>
                <div class="special-cell-title">${esc(s.label)}</div>
                <div class="special-cell-desc">${stripHtml(s.desc||'')}</div>
              </div>
            </div>`;
        });

        // Categories for filter
        const cats = [...new Set(visibleProjects.map(p => p.category))];
        const bar = document.getElementById('filterBar');
        cats.forEach(cat => {
          const btn = document.createElement('button');
          btn.className = 'filter-btn';
          btn.textContent = catLabels[cat] || cat;
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            filterProjects(cat, this);
          });
          bar.appendChild(btn);
        });

        // Check URL param for pre-filter
        const urlFilter = new URLSearchParams(location.search).get('filter');

        // Projects — ryd server-renderet indhold før hydrering (undgår dubletter)
        const grid = document.getElementById('projGrid');
        grid.innerHTML = '';
        const noRes = document.createElement('div');
        noRes.className = 'no-results';
        noRes.id = 'noResults';
        noRes.textContent = 'Ingen projekter i denne kategori endnu.';
        grid.appendChild(noRes);

        visibleProjects.forEach(p => {
          const card = document.createElement('a');
          card.className = 'proj-card';
          card.href = `projects/${p.id}`;
          card.dataset.cat = p.category;
          card.innerHTML = `
            ${p.cover ? `
              <div class="proj-card-img-wrap">
                <img src="${p.cover}" alt="${esc(p.cover_alt||p.title)}" class="proj-card-img" style="object-position: ${p.cover_pos || 'center center'}" loading="lazy" onerror="this.parentElement.style.display='none'">
              </div>
            ` : ''}
            <div class="proj-card-body">
              <div class="tag ${catClass[p.category]||''} proj-tag">${esc(catLabels[p.category]||p.tag||p.category)}</div>
              <div class="proj-title">${esc(p.title)}</div>
              ${p.period ? `<div class="proj-period">${esc(p.period)}</div>` : ''}
              <div class="proj-desc">${esc((p.desc||'').replace(/<\/?[^>]+(>|$)/g, "").trim())}</div>
              ${(p.skills||[]).length ? `<div class="proj-skills">${p.skills.slice(0,4).map(s=>{
                const cleanS = s.replace(/<\/?[^>]+(>|$)/g, "").trim();
                return `<span class="proj-skill">${esc(cleanS)}</span>`;
              }).join('')}</div>` : ''}
              <span class="proj-arrow">Læs mere →</span>
            </div>`;
          grid.insertBefore(card, noRes);
        });

        // Apply URL filter if present
        if (urlFilter && urlFilter !== 'all') {
          const btn = [...bar.querySelectorAll('.filter-btn')].find(b => b.textContent.toLowerCase() === (catLabels[urlFilter]||urlFilter).toLowerCase());
          if (btn) filterProjects(urlFilter, btn);
        }
      });

    function filterProjects(cat, btn) {
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const cards = document.querySelectorAll('.proj-card');
      let visible = 0;
      cards.forEach(c => {
        const show = cat === 'all' || c.dataset.cat === cat;
        c.classList.toggle('hidden', !show);
        if (show) visible++;
      });
      document.getElementById('noResults').classList.toggle('show', visible === 0);
    }
  </script>
</body>
</html>
