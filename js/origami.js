/* ============================================
   ORIGAMI-TRANEN — scroll-indikator på forsiden
   ============================================
   Fuglen letter fra scroll-hintet i hero'en, følger en rute ned gennem
   siden, sætter sig ved hver sektionsoverskrift og folder sig til sidst
   ud til en "kontakt mig"-seddel over CTA-baren.

   - Ruten bygges ud fra de rigtige overskrifter og bygges igen, når
     indholdet fra content.json ændrer sidens højde.
   - Kun transform/opacity animeres, og løkken kører kun mens noget bevæger sig.
   - prefers-reduced-motion: ingen flyvende fugl — sedlen står bare fremme.
   - Scriptet kører før DOMContentLoaded, så main.js' initContactOverlay()
     automatisk kobler sedlens [data-contact] til kontaktformularen. */
(() => {
  'use strict';

  const hero = document.querySelector('.hero');
  const ctaSlot = document.getElementById('global-cta-bar');
  if (!hero || !ctaSlot || !('ResizeObserver' in window)) return;

  const NS = 'http://www.w3.org/2000/svg';
  const mq = window.matchMedia('(prefers-reduced-motion: reduce)');

  /* ---- Fuglens geometri ----
     Hvert hjørne har en position som foldet trane og som fladt papir.
     Ved at interpolere mellem dem kan fuglen foldes ud. */
  const V = {
    A:  [[95, 105],  [120, 256]], B:  [[265, 100], [256, 120]], BK: [[383, 180], [392, 256]],
    T:  [[180, 415], [256, 392]], E:  [[255, 235], [238, 238]], W:  [[222, 262], [196, 270]],
    G:  [[245, 302], [246, 318]], N:  [[292, 302], [304, 306]], S:  [[278, 232], [276, 212]],
    K:  [[302, 188], [310, 190]], H1: [[322, 184], [332, 236]], H2: [[350, 160], [352, 220]],
    H3: [[346, 181], [354, 262]], L:  [[236, 336], [226, 350]], M:  [[266, 322], [282, 352]]
  };
  const FACETS = [
    ['body', ['G', 'M', 'T'], '#8d3cf0'], ['body', ['G', 'L', 'T'], '#a02ff0'],
    ['body', ['W', 'E', 'G'], '#7a55f0'], ['body', ['E', 'S', 'G'], '#5f78f1'],
    ['body', ['S', 'N', 'G'], '#5a86f0'], ['body', ['G', 'N', 'M'], '#6a60ef'],
    ['body', ['S', 'K', 'N'], '#4aa9f1'], ['body', ['K', 'H1', 'N'], '#44bdf2'],
    ['body', ['H1', 'H2', 'H3'], '#46d2f2'], ['body', ['H2', 'BK', 'H3'], '#59e1f3'],
    ['wing-b', ['B', 'E', 'S'], '#48a4f2'], ['wing-b', ['B', 'S', 'K'], '#4fd6f2'],
    ['wing-l', ['A', 'E', 'W'], '#6a5cf2'], ['wing-l', ['A', 'W', 'G'], '#8b3ff1']
  ];

  const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
  const mix = (a, b, t) => a + (b - a) * t;
  const smooth = (a, b, x) => {
    const t = clamp((x - a) / ((b - a) || 1), 0, 1);
    return t * t * (3 - 2 * t);
  };
  const svgEl = (tag, attrs) => {
    const el = document.createElementNS(NS, tag);
    Object.entries(attrs || {}).forEach(([k, v]) => el.setAttribute(k, v));
    return el;
  };

  /* ---- DOM: sedlen (landingspladsen) ----
     En rigtig knap, så den virker med tastatur og skærmlæser. */
  const landing = document.createElement('div');
  landing.className = 'origami-landing';
  const note = document.createElement('button');
  note.type = 'button';
  note.className = 'origami-note';
  note.setAttribute('data-contact', '');
  note.setAttribute('data-track', 'contact-open-origami');
  const noteTitle = document.createElement('span');
  noteTitle.className = 'origami-note-title';
  noteTitle.textContent = 'Skal vi kigge på dit checkout?';
  const noteCta = document.createElement('span');
  noteCta.className = 'origami-note-cta';
  noteCta.textContent = 'Kontakt mig →';
  note.append(noteTitle, noteCta);
  // "Tilbage"-knap: vises kun, når man er fløjet herned ved at klikke på fuglen
  const back = document.createElement('button');
  back.type = 'button';
  back.className = 'origami-back';
  back.hidden = true;
  back.setAttribute('data-track', 'origami-return');
  back.textContent = '↑ Tilbage til hvor du var';
  landing.append(note, back);
  ctaSlot.before(landing);

  /* ---- DOM: rute og fugl ---- */
  const route = svgEl('svg', { class: 'origami-route', 'aria-hidden': 'true', focusable: 'false' });
  const grad = svgEl('linearGradient', { id: 'origamiTrail', gradientUnits: 'userSpaceOnUse', x1: 0, y1: 0, x2: 0, y2: 1000 });
  const stopA = svgEl('stop', { offset: 0, class: 'origami-stop-a' });
  const stopB = svgEl('stop', { offset: 1, class: 'origami-stop-b' });
  grad.append(stopA, stopB);
  const defs = svgEl('defs');
  defs.appendChild(grad);
  const guide = svgEl('path', { class: 'origami-guide' });
  const trail = svgEl('path', { class: 'origami-trail' });
  route.append(defs, guide, trail);

  // Fælles lag for rute og fugl: klipper alt, der stikker ud over skærmkanten
  // (fx den roterede fugl), så siden aldrig kan scrolles sidelæns.
  const layer = document.createElement('div');
  layer.className = 'origami-layer';

  const bird = document.createElement('div');
  bird.className = 'origami-bird is-idle';
  // Fuglen kan klikkes med mus/touch, men holdes ude af tab-rækkefølgen og
  // skærmlæsere: sedlen og CTA-knapperne giver samme vej til kontakt.
  bird.setAttribute('aria-hidden', 'true');
  bird.setAttribute('data-track', 'origami-bird-click');
  bird.title = 'Flyv til kontakt';
  const bob = document.createElement('span');
  bob.className = 'origami-bob';
  const svg = svgEl('svg', { viewBox: '80 90 320 340', focusable: 'false' });
  const sheet = svgEl('polygon', { class: 'origami-sheet', points: '256,120 392,256 256,392 120,256' });
  const groups = {
    'wing-b': svgEl('g', { class: 'origami-wing origami-wing-b' }),
    body: svgEl('g'),
    'wing-l': svgEl('g', { class: 'origami-wing origami-wing-l' })
  };
  svg.append(sheet, groups['wing-b'], groups.body, groups['wing-l']);
  const polys = FACETS.map(([grp, pts, fill]) => {
    const p = svgEl('polygon', { fill });
    groups[grp].appendChild(p);
    return { p, pts };
  });
  bob.appendChild(svg);
  bird.appendChild(bob);

  let lastFold = -1;
  function setFold(t) {
    if (Math.abs(t - lastFold) < 0.002) return;
    lastFold = t;
    polys.forEach(({ p, pts }) => {
      p.setAttribute('points', pts.map(k => {
        const [[bx, by], [fx, fy]] = V[k];
        return (fx + (bx - fx) * t).toFixed(1) + ',' + (fy + (by - fy) * t).toFixed(1);
      }).join(' '));
    });
    sheet.style.opacity = String(Math.max(0, 1 - t * 1.6));
  }
  setFold(1);

  /* ---- Tilstand ---- */
  const state = {
    samples: [], knots: [], total: 0, start: null, end: null,
    arrive: 0, cur: 0, lastY: window.scrollY, vel: 0, dir: 1, face: 1,
    frame: null, active: false, flyUntil: 0, returnTo: null
  };

  // Dokument-position uden transforms (scroll-reveal forskyder sektioner midlertidigt)
  function docTop(el) {
    let y = 0;
    for (let n = el; n; n = n.offsetParent) y += n.offsetTop;
    return y;
  }
  const isNarrow = () => window.innerWidth < 760;
  const birdSize = () => (isNarrow() ? 32 : 54);

  /* ---- Byg ruten ---- */
  function buildRoute() {
    // Ruten må ikke selv holde siden høj, når indholdet krymper
    route.setAttribute('height', 0);
    layer.style.height = '0';
    const W = document.documentElement.clientWidth;
    const H = document.documentElement.scrollHeight;
    const narrow = isNarrow();
    route.setAttribute('width', W);
    route.setAttribute('height', H);
    route.setAttribute('viewBox', `0 0 ${W} ${H}`);
    layer.style.height = H + 'px';
    grad.setAttribute('y2', H);

    // Højre margen: fuglens "motorvej" ned gennem siden
    const lane = W - (narrow ? 22 : 31);

    // Start: ved siden af "Se business cases ↓" — mellem teksten og portrættet.
    // På mobil sidder den i højre kant, hvor portrættet kun er et tonet motiv.
    const heroTop = docTop(hero);
    const cue = hero.querySelector('.hero-btn-secondary');
    let start = { x: W / 2, y: heroTop + hero.offsetHeight - 132 };
    if (cue && cue.offsetParent) {
      const r = cue.getBoundingClientRect();
      start = { x: narrow ? lane : r.right + 48, y: docTop(cue) + cue.offsetHeight / 2 - birdSize() * 0.3 };
    }

    // Hvilepunkter: lige efter teksten i hver synlig sektionsoverskrift.
    // Overskrifts-rækkerne er luftige, så det er her fuglen må krydse siden.
    const perches = [];
    document.querySelectorAll('main .section-title').forEach(h => {
      if (!h.offsetParent || !h.textContent.trim()) return;
      const header = h.closest('.section-header') || h;
      if (narrow) {
        // På mobil fylder kortene hele bredden: fuglen bliver i højre margen
        // og sætter sig på stregen over overskriften
        perches.push({ x: lane, y: docTop(header) - birdSize() * 0.5 });
        return;
      }
      const range = document.createRange();
      range.selectNodeContents(h);
      const r = range.getBoundingClientRect();
      const link = header.querySelector('.section-link');
      const limit = link ? link.getBoundingClientRect().left - 44 : W - 40;
      perches.push({ x: Math.min(r.right + 42, limit), y: docTop(h) + h.offsetHeight / 2 - birdSize() * 0.3 });
    });

    // Slut: midt på sedlen
    // (målt på boksens midte — sedlen er skaleret/roteret om midten, mens den er lukket)
    const nr = note.getBoundingClientRect();
    const end = { x: nr.left + nr.width / 2, y: docTop(note) + note.offsetHeight / 2 };

    /* Ruten: vandret ud til højre margen, lodret ned i margenen og vandret
       ind til næste hvilepunkt. Fuglen krydser altså kun siden i overskrifts-
       rækkerne og kan aldrig parkere oven på brødtekst i et kort. */
    const pts = [start, ...perches.filter(p => p.y > start.y + 40 && p.y < end.y - 40), end];
    let d = `M${start.x},${start.y}`;
    const knots = [{ y: start.y, len: 0, hold: 0 }];
    for (let i = 1; i < pts.length; i++) {
      const p = pts[i - 1], q = pts[i];
      const r = Math.min(70, (q.y - p.y) / 3);
      d += Math.abs(p.x - lane) > 1 ? ` Q${lane},${p.y} ${lane},${p.y + r}` : ` L${lane},${p.y + r}`;
      d += ` L${lane},${q.y - r}`;
      d += Math.abs(q.x - lane) > 1 ? ` Q${lane},${q.y} ${q.x},${q.y}` : ` L${q.x},${q.y}`;
      trail.setAttribute('d', d);
      knots.push({ y: q.y, len: trail.getTotalLength(), hold: i === pts.length - 1 ? 0 : 45 });
    }
    guide.setAttribute('d', d);
    const total = trail.getTotalLength();
    trail.style.strokeDasharray = total;

    // Opslagstabel over punkter langs ruten, så tick() ikke skal måle hver frame
    const n = 800, samples = [];
    for (let i = 0; i <= n; i++) {
      const len = total * i / n;
      const pt = trail.getPointAtLength(len);
      samples.push({ len, x: pt.x, y: pt.y });
    }
    Object.assign(state, { samples, total, start, end, knots });
    state.cur = lenForScroll();
    // Ændrer siden højde, mens fuglen flyver til kontakt, rettes målet til
    if (performance.now() < state.flyUntil) flyToNote();
    requestTick();
  }

  function segmentAt(len) {
    const s = state.samples, n = s.length - 1;
    const i = clamp(Math.floor(len / state.total * n), 0, n - 1);
    return [s[i], s[i + 1]];
  }

  /* Fuglen følger et punkt midt i skærmen. I starten sidder den i hero'en,
     og den når sedlen, når sedlen står midt i skærmen (eller lige før
     bunden, hvis siden er for kort til det). Ved hvert hvilepunkt står
     fuglen stille et stykke scroll og bremser blødt op før og efter. */
  function lenForScroll() {
    const vh = window.innerHeight;
    const sy = window.scrollY;
    const maxScroll = document.documentElement.scrollHeight - vh;
    state.arrive = Math.max(0, Math.min(state.end.y - vh * 0.5, maxScroll - vh * 0.22));
    let y = sy + vh * 0.5;
    y += (state.start.y - vh * 0.5) * (1 - smooth(0, vh * 0.5, sy));
    y += (state.end.y - (state.arrive + vh * 0.5)) * smooth(state.arrive - vh * 0.6, state.arrive, sy);

    const k = state.knots;
    if (y <= k[0].y) return 0;
    for (let i = 0; i < k.length - 1; i++) {
      const a = k[i], b = k[i + 1];
      if (y <= a.y + a.hold) return a.len;
      if (y < b.y - b.hold) {
        const t = (y - (a.y + a.hold)) / ((b.y - b.hold) - (a.y + a.hold));
        return mix(a.len, b.len, 0.5 - 0.5 * Math.cos(Math.PI * t));
      }
    }
    return state.total;
  }

  /* ---- Animationsløkke ---- */
  function requestTick() {
    if (state.active && !state.frame) state.frame = requestAnimationFrame(tick);
  }

  function tick() {
    state.frame = null;
    if (!state.total) return;
    const sy = window.scrollY;
    const dy = sy - state.lastY;
    state.lastY = sy;
    state.vel = mix(state.vel, dy, 0.25);
    if (Math.abs(dy) > 0.5) state.dir = dy > 0 ? 1 : -1;

    const target = lenForScroll();
    state.cur = mix(state.cur, target, 0.14);
    const settled = Math.abs(target - state.cur) < 0.5;
    if (settled) state.cur = target;
    trail.style.strokeDashoffset = state.total - state.cur;

    const [a, b] = segmentAt(state.cur);
    const t = clamp((state.cur - a.len) / ((b.len - a.len) || 1), 0, 1);
    const size = birdSize();
    // Hold fuglen inden for skærmens bredde, så mobilen aldrig kan scrolle sidelæns.
    // Ekstra luft, fordi den roterede (og vuggende) fugl er bredere end sin boks.
    const edge = size * 0.75 + 2;
    const x = clamp(mix(a.x, b.x, t), edge, document.documentElement.clientWidth - edge);
    const y = mix(a.y, b.y, t);
    // Næbbet peger den vej, fuglen flyver — også når man scroller op
    const dx = (b.x - a.x) * state.dir, dyp = (b.y - a.y) * state.dir;
    if (Math.abs(dx) > 0.2) state.face = dx < 0 ? -1 : 1; // lodret: behold retningen
    const face = state.face;
    const heading = clamp(Math.atan2(dyp, Math.abs(dx) + 0.001) * 180 / Math.PI * 0.55, -32, 32);

    // Udfoldning: starter når fuglen er landet og man scroller lidt videre
    const vh = window.innerHeight;
    const maxScroll = document.documentElement.scrollHeight - vh;
    const unfoldEnd = Math.min(state.arrive + vh * 0.2, maxScroll);
    const u = state.cur >= state.total - 1 ? smooth(state.arrive - 4, unfoldEnd, sy) : 0;
    const fold = 1 - smooth(0, 0.6, u);
    const scale = 1 + smooth(0.25, 0.85, u) * (isNarrow() ? 2.6 : 3.2);

    setFold(fold);
    bird.style.opacity = String(1 - smooth(0.78, 1, u));
    bird.style.width = bird.style.height = size + 'px';
    bird.style.transform = fold < 0.5
      ? `translate(${x - size / 2}px, ${y - size / 2}px) scale(${scale}) rotate(${-8 * (1 - fold)}deg)`
      : `translate(${x - size / 2}px, ${y - size * 0.62}px) scaleX(${face}) rotate(${heading}deg)`;
    landing.classList.toggle('is-open', u > 0.8);
    bird.classList.toggle('is-unfolding', u > 0); // kan ikke klikkes, mens den bliver til sedlen

    const perched = fold > 0.95 && settled && Math.abs(state.vel) < 2 &&
      state.knots.some(k => k.hold && Math.abs(state.cur - k.len) < 1);
    bird.classList.toggle('is-perched', perched);
    // "Hviler": sidder ved en overskrift eller ved starten i hero'en.
    // På touch-skærme kan fuglen kun trykkes på her (se CSS).
    bird.classList.toggle('is-resting', perched || (settled && state.cur < 1));
    const flapping = !perched && fold > 0.95 && (!settled || Math.abs(state.vel) > 1);
    bird.classList.toggle('is-flapping', flapping);
    bird.style.setProperty('--flap', clamp(0.7 - Math.abs(state.vel) * 0.012, 0.22, 0.7) + 's');

    if (!settled || Math.abs(state.vel) > 0.3) requestTick();
    else {
      bird.classList.remove('is-flapping');
      bird.classList.toggle('is-idle', u === 0);
    }
    if (flapping) bird.classList.remove('is-idle');
  }

  /* ---- Klik på fuglen: en looping, og så flyver den (og siden) ned til sedlen ---- */
  bird.addEventListener('click', () => {
    if (bird.classList.contains('is-unfolding')) return;
    bob.classList.remove('is-looping');
    void bob.offsetWidth; // genstart animationen ved hurtige gentagne klik
    bob.classList.add('is-looping');
    state.flyUntil = performance.now() + 3000;
    // Husk hvor man var — både til knappen og til browserens tilbage-knap,
    // ligesom et almindeligt ankerlink. Uden det ville "tilbage" forlade siden.
    // Positionen opdateres ved hvert klik og gemmes også i historikken, så den
    // overlever en genindlæsning eller et besøg på en anden side.
    state.returnTo = window.scrollY;
    if (!isOrigamiEntry(history.state)) {
      history.replaceState({ ...(history.state || {}), origamiReturn: state.returnTo }, '');
      // Browseren ville ellers selv genskabe sin gamle position efter popstate
      // og overskrive vores (nyere) position. Kun for dette ene historik-punkt:
      history.scrollRestoration = 'manual';
      history.pushState({ origami: true }, '');
      history.scrollRestoration = 'auto';
    }
    back.hidden = false;
    flyToNote();
  });
  const isOrigamiEntry = s => !!(s && s.origami);

  // Tilbage: via knappen går vi baglæns i historikken, så knap og browserens
  // tilbage-knap altid gør det samme (og historikken ikke vokser).
  back.addEventListener('click', () => {
    if (isOrigamiEntry(history.state)) history.back();
    else returnHome(state.returnTo);
  });
  window.addEventListener('popstate', e => {
    if (isOrigamiEntry(e.state)) return; // "frem" til sedlen: browseren klarer det
    // Nyeste klik vinder; ellers positionen gemt i historikken (efter reload)
    const saved = e.state && typeof e.state.origamiReturn === 'number' ? e.state.origamiReturn : null;
    returnHome(state.returnTo !== null ? state.returnTo : saved);
  });
  function returnHome(y) {
    state.flyUntil = 0;
    state.returnTo = null;
    back.hidden = true;
    if (y !== null && y !== undefined) window.scrollTo({ top: y, behavior: mq.matches ? 'auto' : 'smooth' });
  }
  // Scroller sedlen så langt op, at fuglen er landet og foldet helt ud
  function flyToNote() {
    const vh = window.innerHeight;
    const maxScroll = document.documentElement.scrollHeight - vh;
    window.scrollTo({ top: Math.min(state.arrive + vh * 0.2, maxScroll), behavior: 'smooth' });
  }
  // Tager brugeren selv over (hjul/touch/taster), stopper vi med at rette målet
  const cancelFly = () => { state.flyUntil = 0; };
  bob.addEventListener('animationend', e => {
    if (e.animationName === 'origamiLoop') bob.classList.remove('is-looping');
  });

  /* ---- Til/fra efter brugerens bevægelsespræference ---- */
  let rTimer = null;
  const rebuild = () => { clearTimeout(rTimer); rTimer = setTimeout(buildRoute, 150); };
  const ro = new ResizeObserver(rebuild);

  function enable() {
    state.active = true;
    document.documentElement.classList.add('has-origami');
    layer.append(route, bird);
    document.body.append(layer);
    ['wheel', 'touchstart', 'keydown'].forEach(ev => window.addEventListener(ev, cancelFly, { passive: true }));
    ro.observe(document.body);
    window.addEventListener('scroll', requestTick, { passive: true });
    window.addEventListener('resize', rebuild);
    buildRoute();
  }
  function disable() {
    state.active = false;
    if (state.frame) cancelAnimationFrame(state.frame);
    state.frame = null;
    document.documentElement.classList.remove('has-origami');
    layer.remove();
    ['wheel', 'touchstart', 'keydown'].forEach(ev => window.removeEventListener(ev, cancelFly));
    ro.disconnect();
    window.removeEventListener('scroll', requestTick);
    window.removeEventListener('resize', rebuild);
    landing.classList.add('is-open'); // uden animation står sedlen bare fremme
  }
  const apply = () => (mq.matches ? disable() : enable());

  const boot = () => {
    apply();
    mq.addEventListener('change', apply);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(() => state.active && buildRoute());
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
