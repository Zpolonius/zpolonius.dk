<?php
// Log evt. AI-bot crawl (fejler lydløst)
@include __DIR__ . '/api/log_ai_bot.php';

$h = function ($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); };
$data = json_decode(@file_get_contents(__DIR__ . '/data/content.json'), true) ?: [];
$om = $data['om'] ?? [];
$site = $data['site'] ?? [];
$bio = $om['bio'] ?? '';
$bioParas = array_values(array_filter(array_map('trim', preg_split('/\n\n+/', $bio))));
$firstPara = $bioParas[0] ?? '';
$facts = $om['facts'] ?? [];
$komp = $om['kompetencer'] ?? [];
$sprog = $om['sprog'] ?? [];
$interesser = $om['interesser'] ?? [];
$social = $site['social'] ?? [];
$faqs = [
  ['q' => 'Hvem er Zacharias Polonius?', 'a' => 'Zacharias Polonius er Technical Account Manager hos Bring og specialist i checkout-optimering. Han bygger bro mellem kompleks teknologi og målbar forretningsværdi og har hjulpet over 100 webshops med at øge deres konvertering gennem data og AI.'],
  ['q' => 'Hvad er checkout-optimering?', 'a' => 'Checkout-optimering handler om at fjerne friktion i den sidste og mest kritiske del af købsrejsen, så færre kunder forlader kurven og flere gennemfører købet. Det kombinerer dataanalyse, teknisk indsigt og brugeroplevelse.'],
  ['q' => 'Hvad laver en Technical Account Manager hos Bring?', 'a' => 'Som Technical Account Manager rådgiver Zacharias Brings kunder, så de får mest muligt ud af samarbejdet — fra teknisk integration og levering til optimering af deres e-commerce- og checkout-flow.'],
  ['q' => 'Hvordan kan Zacharias hjælpe min webshop?', 'a' => 'Gennem checkout- og konverteringsoptimering, AI-løsninger til teknisk support og kundesucces samt teknisk rådgivning. Du kan booke et uforpligtende checkout-review via kontaktsiden.'],
  ['q' => 'Hvordan kommer jeg i kontakt med Zacharias?', 'a' => 'Du kan skrive til zacharias@polonius.dk, ringe på +45 30 68 70 41 eller finde ham på LinkedIn: linkedin.com/in/zpolonius/.'],
];
?>
<!DOCTYPE html>
<html lang="da">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
  <base href="/">
  <title>Om Mig — Zacharias Polonius</title>
  <meta name="description" content="Lær mere om Zacharias Polonius — Technical Account Manager hos Bring — min baggrund, min filosofi og min passion for at bygge bro mellem teknik og forretning.">
  
  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://zpolonius.dk/about">
  <meta property="og:description" content="Bygger bro mellem tech og forretning. Speciale i checkout-optimering og AI.">
  <meta property="og:image" content="https://zpolonius.dk/assets/og-about.jpg">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="https://zpolonius.dk/about">
  <meta property="twitter:title" content="Om Zacharias Polonius — Checkout Arkitekt">
  <meta property="twitter:description" content="Bygger bro mellem tech og forretning. Speciale i checkout-optimering og AI.">
  <meta property="twitter:image" content="https://zpolonius.dk/assets/og-about.jpg">
  <link rel="canonical" href="https://zpolonius.dk/about">

  <!-- Structured Data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "Zacharias Polonius",
    "url": "https://zpolonius.dk/",
    "email": "zacharias@polonius.dk",
    "telephone": "+4530687041",
    "jobTitle": "Technical Account Manager",
    "worksFor": { "@type": "Organization", "name": "Bring", "url": "https://www.bring.dk/" },
    "knowsAbout": ["Checkout-optimering", "Konverteringsoptimering (CRO)", "E-commerce", "AI-drevet kundesucces"],
    "sameAs": [
      "https://www.linkedin.com/in/zpolonius/",
      "https://github.com/zpolonius",
      "https://www.instagram.com/zackp91/"
    ]
  }
  </script>
<?php
$faqSchema = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
foreach ($faqs as $f) {
  $faqSchema['mainEntity'][] = ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]];
}
?>
  <script type="application/ld+json"><?= json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* SPLIT HERO */
    .about-hero {
      display: grid; grid-template-columns: 1fr 400px;
      border-bottom: 0.5px solid var(--border); min-height: 480px;
    }
    .about-hero-left {
      padding: 52px 40px; border-right: 0.5px solid var(--border);
      display: flex; flex-direction: column; justify-content: space-between;
    }
    .about-hero-label { font-size: 10px; letter-spacing: 0.15em; text-transform: uppercase; color: var(--blue); margin-bottom: 14px; }
    .about-hero-name  { font-size: 44px; font-weight: 700; letter-spacing: -0.04em; color: var(--text); line-height: 1.0; margin-bottom: 20px; }
    .about-hero-bio   { font-size: 15px; color: var(--text-muted); line-height: 1.8; max-width: 480px; }
    .about-hero-actions { display: flex; gap: 12px; margin-top: 32px; }
    .about-hero-btn-p { background: var(--blue); color: #fff; border: none; padding: 10px 22px; font-size: 13px; font-weight: 500; cursor: pointer; font-family: var(--font); transition: opacity 0.2s; border-radius: 100px; }
    .about-hero-btn-p:hover { opacity: 0.85; }
    .about-hero-btn-s { background: transparent; color: var(--text-muted); border: 0.5px solid var(--border-md); padding: 10px 22px; font-size: 13px; font-weight: 500; font-family: var(--font); transition: all 0.2s; display: inline-block; border-radius: 100px; }
    .about-hero-btn-s:hover { color: var(--text); border-color: var(--text-muted); }

    .about-hero-right {
      position: relative; overflow: hidden; background: var(--bg-card);
    }
    .about-photo { width: 100%; height: 100%; object-fit: cover; object-position: center top; filter: grayscale(20%); }
    .about-photo-fallback {
      width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
      background: var(--bg-card);
    }
    .about-photo-initials {
      font-size: 80px; font-weight: 700; letter-spacing: -0.05em;
      color: var(--border-md); line-height: 1;
    }

    /* INFO GRID */
    .info-grid { display: grid; grid-template-columns: repeat(4, 1fr); border-bottom: 0.5px solid var(--border); }
    .info-cell { padding: 22px 28px; border-right: 0.5px solid var(--border); }
    .info-cell:last-child { border-right: none; }
    .info-cell-label { font-size: 9px; letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-faint); margin-bottom: 8px; }
    .info-cell-val   { font-size: 14px; color: var(--text-muted); line-height: 1.6; }
    .info-cell-val strong { color: var(--text); font-weight: 500; display: block; font-size: 14px; margin-bottom: 2px; }

    /* BIO SECTION */
    .bio-wrap { display: grid; grid-template-columns: 1fr 300px; border-bottom: 0.5px solid var(--border); }
    .bio-main { padding: 40px; border-right: 0.5px solid var(--border); }
    .bio-para { font-size: 15px; color: var(--text-muted); line-height: 1.85; margin-bottom: 20px; }
    .bio-para:last-child { margin-bottom: 0; }
    .bio-sidebar { padding: 40px 28px; }
    .bio-sidebar-block { margin-bottom: 24px; }
    .bio-sidebar-label { font-size: 9px; letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-faint); margin-bottom: 10px; }
    .bio-sidebar-item { font-size: 12px; color: var(--text-muted); padding: 5px 0; border-bottom: 0.5px solid var(--border); }
    .bio-sidebar-item:last-child { border-bottom: none; }

    /* PERSONTYPE */
    .persontype-wrap { display: grid; grid-template-columns: 1fr 1fr; border-bottom: 0.5px solid var(--border); }
    .persontype-cell { padding: 32px 40px; border-right: 0.5px solid var(--border); }
    .persontype-cell:last-child { border-right: none; }
    .persontype-title { font-size: 16px; font-weight: 600; color: var(--text); letter-spacing: -0.02em; margin-bottom: 12px; }
    .persontype-desc  { font-size: 13px; color: var(--text-muted); line-height: 1.75; }
    .persontype-tags  { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 14px; }

    /* SKILLS STRIP */
    .skills-strip { display: grid; grid-template-columns: repeat(4, 1fr); border-bottom: 0.5px solid var(--border); }
    .skill-group { padding: 22px 28px; border-right: 0.5px solid var(--border); }
    .skill-group:last-child { border-right: none; }
    .skill-group-title { font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-faint); margin-bottom: 12px; }
    .skill-tags { display: flex; flex-wrap: wrap; gap: 5px; }

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
      /* HERO — stack photo below text */
      .about-hero {
        grid-template-columns: 1fr;
        min-height: auto;
      }
      .about-hero-left {
        padding: 32px 20px 28px;
        border-right: none;
        border-bottom: 0.5px solid var(--border);
      }
      .about-hero-name { font-size: 32px; }
      .about-hero-right { height: 280px; }
      .about-hero-actions { flex-direction: column; gap: 8px; }
      .about-hero-btn-p, .about-hero-btn-s { text-align: center; }

      /* INFO GRID — 2 columns */
      .info-grid { grid-template-columns: 1fr 1fr; }
      .info-cell:nth-child(2n) { border-right: none; }
      .info-cell:nth-child(n+3) { border-top: 0.5px solid var(--border); }
      .info-cell { padding: 16px 20px; }

      /* BIO — stack */
      .bio-wrap { grid-template-columns: 1fr; }
      .bio-main { padding: 24px 20px; border-right: none; border-bottom: 0.5px solid var(--border); }
      .bio-sidebar { padding: 24px 20px; }
      .bio-para { font-size: 14px; }

      /* PERSONTYPE — stack */
      .persontype-wrap { grid-template-columns: 1fr; }
      .persontype-cell { padding: 24px 20px; border-right: none; border-bottom: 0.5px solid var(--border); }
      .persontype-cell:last-child { border-bottom: none; }

      /* SKILLS STRIP — 2 columns */
      .skills-strip { grid-template-columns: 1fr 1fr; }
      .skill-group:nth-child(2n) { border-right: none; }
      .skill-group:nth-child(n+3) { border-top: 0.5px solid var(--border); }
      .skill-group { padding: 16px 20px; }
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

  <!-- HERO -->
  <main>
  <div class="about-hero">
    <div class="about-hero-left fade-up fade-up-1">
      <div>
        <div class="about-hero-label">Om mig</div>
        <h1 class="about-hero-name" id="heroName">Zacharias<br>Polonius</h1>
        <div id="statusPillContainer"></div>
        <p class="about-hero-bio" id="heroBio"><?= $h($firstPara) ?: 'Indlæser...' ?></p>
        <div class="about-hero-actions">
          <button class="about-hero-btn-p" data-contact>Tag kontakt →</button>
          <a href="cv" class="about-hero-btn-s">Se CV →</a>
        </div>
      </div>
    </div>
    <div class="about-hero-right fade-up fade-up-2">
      <img class="about-photo" id="aboutPhoto" src="" alt="Zacharias Polonius"
        onerror="this.style.display='none'; document.getElementById('photoFallback').style.display='flex';">
      <div class="about-photo-fallback" id="photoFallback" style="display:none;">
        <div class="about-photo-initials">ZP</div>
      </div>
    </div>
  </div>

  <!-- INFO STRIP -->
  <div class="info-grid" id="infoGrid">
<?php foreach ($facts as $c): ?>
    <div class="info-cell">
      <div class="info-cell-label"><?= $h($c['label'] ?? '') ?></div>
      <div class="info-cell-val"><strong><?= $h($c['val'] ?? '') ?></strong><?= $h($c['sub'] ?? '') ?></div>
    </div>
<?php endforeach; ?>
  </div>

  <!-- BIO + SIDEBAR -->
  <div class="bio-wrap">
    <div class="bio-main">
      <div class="section-lbl" style="font-size:9px;letter-spacing:0.15em;text-transform:uppercase;color:var(--text-faint);padding-bottom:14px;border-bottom:0.5px solid var(--border);margin-bottom:22px;">Biografi</div>
      <div id="bioContent">
<?php foreach ($bioParas as $p): ?>
        <p class="bio-para"><?= $h($p) ?></p>
<?php endforeach; ?>
      </div>
    </div>
    <div class="bio-sidebar">
      <div class="bio-sidebar-block">
        <div class="bio-sidebar-label">Sprog</div>
        <div id="sprogList"><?php foreach ($sprog as $s): ?><div class="bio-sidebar-item"><?= $h($s) ?></div><?php endforeach; ?></div>
      </div>
      <div class="bio-sidebar-block">
        <div class="bio-sidebar-label">Interesser</div>
        <div id="interesserList"><?php foreach ($interesser as $i): ?><div class="bio-sidebar-item"><?= $h($i) ?></div><?php endforeach; ?></div>
      </div>
      <div class="bio-sidebar-block">
        <div class="bio-sidebar-label">Sociale medier</div>
        <div id="socialList"><?php if (!empty($social['linkedin'])): ?><div class="bio-sidebar-item"><a href="<?= $h($social['linkedin']) ?>" target="_blank" style="color:var(--blue);">LinkedIn →</a></div><?php endif; ?><?php if (!empty($social['instagram'])): ?><div class="bio-sidebar-item"><a href="<?= $h($social['instagram']) ?>" target="_blank" style="color:var(--blue);">Instagram →</a></div><?php endif; ?><?php if (!empty($social['facebook'])): ?><div class="bio-sidebar-item"><a href="<?= $h($social['facebook']) ?>" target="_blank" style="color:var(--blue);">Facebook →</a></div><?php endif; ?></div>
      </div>
    </div>
  </div>

  <!-- SKILLS -->
  <div class="section-header">
    <h2 class="section-title">Faglige kompetencer</h2>
  </div>
  <div class="skills-strip" id="skillsStrip">
    <!-- Rendered dynamically by JS -->
  </div>

  <!-- PERSONTYPE -->
  <div class="section-header">
    <h2 class="section-title">Min Tilgang & Filosofi</h2>
  </div>
  <div class="persontype-wrap">
    <div class="persontype-cell">
      <div class="persontype-title">Mennesket før Teknik</div>
      <div class="persontype-desc">Teknologi er kun et værktøj. Jeg tror på, at de bedste løsninger skabes, når vi forstår de mennesker, der skal bruge dem. Min baggrund fra service-branchen sikrer, at jeg aldrig glemmer brugeroplevelsen.</div>
    </div>
    <div class="persontype-cell">
      <div class="persontype-title">Brobyggeren</div>
      <div class="persontype-desc">Jeg elsker at oversætte komplekse tekniske udfordringer til forretningsmuligheder. Jeg taler både flydende 'udvikler' og 'ledelse', hvilket gør mig til den ideelle bindeled i ethvert projekt.</div>
    </div>
  </div>
  </div>
  <div class="persontype-wrap">
    <div class="persontype-cell">
      <div class="persontype-title">Analytisk og løsningsorienteret</div>
      <p class="persontype-desc" id="persontypeDesc">Indlæser...</p>
      <div class="persontype-tags" id="persontypeTags">
        <span class="skill-tag blue">Analytisk</span>
        <span class="skill-tag blue">Strategisk</span>
        <span class="skill-tag">Detaljeorienteret</span>
        <span class="skill-tag">Løsningsfokuseret</span>
        <span class="skill-tag">Kommunikativ</span>
      </div>
    </div>
    <div class="persontype-cell">
      <div class="persontype-title">Amatørfotograf</div>
      <p class="persontype-desc">Udover arbejdet er jeg passioneret amatørfotograf. Jeg finder inspiration i hverdagens detaljer og bruger kameraet til at se verden fra nye vinkler. Solbrillerne på forsiden er et af mine foretrukne motiver — detaljer der fortæller en større historie.</p>
      <div class="persontype-tags">
        <span class="skill-tag">Makrofotografi</span>
        <span class="skill-tag">Hverdagsmotiver</span>
        <span class="skill-tag">Detaljer</span>
      </div>
    </div>
  </div>

  <!-- FAQ -->
  <style>
    .faq-section { border-top: 0.5px solid var(--border); }
    .faq-item { border-bottom: 0.5px solid var(--border); }
    .faq-q { padding: 22px 40px; font-size: 15px; font-weight: 600; color: var(--text); cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 20px; transition: color 0.2s; }
    .faq-q:hover { color: var(--blue); }
    .faq-q::-webkit-details-marker { display: none; }
    .faq-q::after { content: '+'; color: var(--blue); font-size: 22px; font-weight: 400; flex-shrink: 0; }
    details[open] .faq-q::after { content: '−'; }
    .faq-a { padding: 0 40px 24px; font-size: 14px; color: var(--text-muted); line-height: 1.8; max-width: 760px; }
    @media (max-width: 768px) { .faq-q { padding: 18px 20px; } .faq-a { padding: 0 20px 18px; } }
  </style>
  <div class="section-header">
    <h2 class="section-title">Ofte stillede spørgsmål</h2>
  </div>
  <div class="faq-section">
<?php foreach ($faqs as $f): ?>
    <details class="faq-item">
      <summary class="faq-q"><?= $h($f['q']) ?></summary>
      <div class="faq-a"><?= $h($f['a']) ?></div>
    </details>
<?php endforeach; ?>
  </div>

  <!-- GLOBAL BENTO -->
  <div id="global-bento"></div>
  <div id="global-detail-panel"></div>

  </main>

  <!-- CENTRAL CTA BAR -->
  <div id="global-cta-bar"></div>

  <!-- SHARED FOOTER -->
  <footer id="global-footer" class="footer"></footer>


  <!-- CONTACT OVERLAY PLACEHOLDER REMOVED - NOW IN MAIN.JS -->
  <!-- BOTTOM NAV PLACEHOLDER REMOVED - NOW IN MAIN.JS -->
  
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

    fetch('data/content.json?t=' + Date.now())
      .then(r => r.json())
      .then(data => {
        const om = data.om || {};
        const site = data.site || {};
        const h = data.hero || {};

        // Hero Name
        if (site.name) document.getElementById('heroName').innerHTML = site.name.replace(' ', '<br>');

        // Photo
        const aPhoto = document.getElementById('aboutPhoto');
        const aFallback = document.getElementById('photoFallback');
        if (om.photo) {
          aPhoto.src = om.photo;
          aPhoto.style.display = 'block';
          if (aFallback) aFallback.style.display = 'none';
          if (om.photo_pos) aPhoto.style.objectPosition = om.photo_pos;
        } else {
          aPhoto.style.display = 'none';
          if (aFallback) aFallback.style.display = 'flex';
        }

        // Bio paragraphs
        const bioEl = document.getElementById('bioContent');
        bioEl.innerHTML = ''; // Clear indlæser...
        if (om.bio) {
          om.bio.split('\n\n').forEach(p => {
            if (p.trim()) bioEl.innerHTML += `<p class="bio-para">${esc(p.trim())}</p>`;
          });
        }

        // Hero bio — first paragraph
        const firstPara = (om.bio || '').split('\n\n')[0] || '';
        document.getElementById('heroBio').textContent = firstPara;
        
        // Status Pill
        if (h.status) {
          document.getElementById('statusPillContainer').innerHTML = `
            <div class="status-pill" style="margin-bottom: 24px; border-color: var(--border-blue); color: var(--blue);">
              <span class="status-dot"></span> ${esc(h.status)}
            </div>`;
        }

        // Info Grid
        const info = document.getElementById('infoGrid');
        const infoCells = om.facts || [
          { label: 'Bopæl', val: site.location || 'Danmark', sub: 'Sjælland' },
          { label: 'Familie', val: 'Gift', sub: 'To sønner' },
          { label: 'Rolle', val: 'Checkout Arkitekt', sub: 'Bring · 2023 — nu' },
          { label: 'Erfaring', val: '10+ år', sub: 'E-commerce & AI' }
        ];
        info.innerHTML = infoCells.map(c => `
          <div class="info-cell">
            <div class="info-cell-label">${esc(c.label)}</div>
            <div class="info-cell-val"><strong>${esc(c.val)}</strong>${esc(c.sub)}</div>
          </div>
        `).join('');

        // Sprog
        const sprogEl = document.getElementById('sprogList');
        sprogEl.innerHTML = '';
        (om.sprog || ['Dansk (modersmål)', 'Engelsk (flydende)']).forEach(s => {
          sprogEl.innerHTML += `<div class="bio-sidebar-item">${esc(s)}</div>`;
        });

        // Interesser
        const intEl = document.getElementById('interesserList');
        intEl.innerHTML = '';
        (om.interesser || []).forEach(i => {
          intEl.innerHTML += `<div class="bio-sidebar-item">${esc(i)}</div>`;
        });

        // Social
        const socEl = document.getElementById('socialList');
        socEl.innerHTML = '';
        const socials = site.social || {};
        if (socials.linkedin)  socEl.innerHTML += `<div class="bio-sidebar-item"><a href="${socials.linkedin}"  target="_blank" style="color:var(--blue);">LinkedIn →</a></div>`;
        if (socials.instagram) socEl.innerHTML += `<div class="bio-sidebar-item"><a href="${socials.instagram}" target="_blank" style="color:var(--blue);">Instagram →</a></div>`;
        if (socials.facebook)  socEl.innerHTML += `<div class="bio-sidebar-item"><a href="${socials.facebook}"  target="_blank" style="color:var(--blue);">Facebook →</a></div>`;

        // Kompetencer (Dynamic)
        const ks = document.getElementById('skillsStrip');
        if (ks) {
          const komp = data.om?.kompetencer || [];
          ks.innerHTML = komp.map(grp => `
            <div class="skill-group">
              <div class="skill-group-title">${esc(grp.category)}</div>
              <div class="skill-tags">
                ${grp.tags.map(t => {
                  const isBlue = ["ai", "tech", "checkout", "api", "flutter", "coding", "software", "sql", "xml", "cro"].some(key => grp.category.toLowerCase().includes(key) || t.toLowerCase().includes(key));
                  return `<span class="skill-tag ${isBlue?'blue':''}">${esc(t)}</span>`;
                }).join('')}
              </div>
            </div>
          `).join('');
        }
      });
  </script>
</body>
</html>
