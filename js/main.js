/* ============================================
   Z. POLONIUS — SHARED JAVASCRIPT
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initActiveNav();
  initContactOverlay();
  initHamburger();
  initBottomNav();
});

/* ---- THEME ---- */
function initTheme() {
  const saved = localStorage.getItem('zp-theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

  if (saved) {
    document.documentElement.setAttribute('data-theme', saved);
  } else if (!prefersDark) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
  updateThemeIcon();

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    if (!localStorage.getItem('zp-theme')) {
      document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
      updateThemeIcon();
    }
  });
}

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme');
  const next = current === 'light' ? 'dark' : 'light';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('zp-theme', next);
  updateThemeIcon();
}

function updateThemeIcon() {
  const isDark = document.documentElement.getAttribute('data-theme') !== 'light';
  document.querySelectorAll('.theme-switch').forEach(btn => {
    btn.textContent = isDark ? '☀' : '☽';
    btn.title = isDark ? 'Skift til lys tilstand' : 'Skift til mørk tilstand';
  });
}

/* ---- ACTIVE NAV ---- */
function initActiveNav() {
  const page = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-links a').forEach(a => {
    if (a.getAttribute('href') === page) a.classList.add('active');
  });
}

/* ---- HAMBURGER (skjult på mobil — bottom nav bruges i stedet) ---- */
function initHamburger() {
  const btn   = document.getElementById('hamburgerBtn');
  const links = document.querySelector('.nav-links');
  if (!btn || !links) return;

  btn.addEventListener('click', () => {
    const open = links.classList.toggle('open');
    btn.textContent = open ? '✕' : '☰';
    btn.setAttribute('aria-expanded', open);
  });

  links.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => {
      links.classList.remove('open');
      btn.textContent = '☰';
    });
  });

  document.addEventListener('click', e => {
    if (!btn.contains(e.target) && !links.contains(e.target)) {
      links.classList.remove('open');
      btn.textContent = '☰';
    }
  });
}

/* ---- FLOATING BOTTOM NAV ---- */
function initBottomNav() {
  const nav = document.getElementById('bottomNav');
  if (!nav) return;

  const page = location.pathname.split('/').pop() || 'index.html';
  const map = {
    'index.html':    'hjem',
    '':              'hjem',
    'projects.html': 'projekter',
    'cv.html':       'cv',
    'about.html':    'om',
    'contact.html':  'kontakt',
  };
  const activeKey = map[page];
  if (activeKey) {
    nav.querySelector(`[data-page="${activeKey}"]`)?.classList.add('active');
  }

  nav.querySelector('[data-contact-mobile]')?.addEventListener('click', openContact);
}

/* ---- CONTACT OVERLAY ---- */
function initContactOverlay() {
  const overlay = document.getElementById('contactOverlay');
  if (!overlay) return;

  document.querySelectorAll('[data-contact]').forEach(btn => {
    btn.addEventListener('click', openContact);
  });

  overlay.querySelector('.overlay-bg')?.addEventListener('click', closeContact);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeContact();
  });

  overlay.querySelectorAll('.pill').forEach(pill => {
    pill.addEventListener('click', () => {
      overlay.querySelectorAll('.pill').forEach(p => p.classList.remove('selected'));
      pill.classList.add('selected');
      updateProgress();
    });
  });

  ['f-name','f-email','f-msg'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', updateProgress);
  });

  document.getElementById('contactSubmit')?.addEventListener('click', submitContact);
}

function openContact() {
  // Reset formular til frisk start
  const formView    = document.getElementById('contactFormView');
  const successView = document.getElementById('contactSuccessView');
  const submitBtn   = document.getElementById('contactSubmit');
  if (formView)    formView.style.display = '';
  if (successView) successView.classList.remove('show');
  if (submitBtn)   { submitBtn.classList.remove('sending'); submitBtn.textContent = 'Send besked →'; }

  const bar = document.getElementById('contactProgressBar');
  if (bar) bar.style.width = '0%';
  document.querySelectorAll('#contactOverlay .pill').forEach(p => p.classList.remove('selected'));
  ['f-name','f-email','f-company','f-msg'].forEach(id => {
    const el = document.getElementById(id);
    if (el) { el.value = ''; el.classList.remove('error','valid'); }
  });

  document.getElementById('contactOverlay')?.classList.add('active');
  document.body.style.overflow = 'hidden';
  setTimeout(() => document.getElementById('f-name')?.focus(), 400);
}

function closeContact() {
  document.getElementById('contactOverlay')?.classList.remove('active');
  document.body.style.overflow = '';
}

function updateProgress() {
  const vals = [
    document.getElementById('f-name')?.value.trim(),
    document.getElementById('f-email')?.value.trim(),
    document.getElementById('f-msg')?.value.trim(),
    document.querySelector('#contactOverlay .pill.selected') ? 'x' : ''
  ];
  const pct = (vals.filter(v => v && v.length).length / vals.length) * 100;
  const bar = document.getElementById('contactProgressBar');
  if (bar) bar.style.width = pct + '%';

  const emailEl = document.getElementById('f-email');
  if (emailEl) emailEl.classList.toggle('valid', emailEl.value.includes('@'));
}

function submitContact() {
  const name  = document.getElementById('f-name')?.value.trim();
  const email = document.getElementById('f-email')?.value.trim();
  const msg   = document.getElementById('f-msg')?.value.trim();
  let valid = true;

  [['f-name', name], ['f-email', email], ['f-msg', msg]].forEach(([id, val]) => {
    const el = document.getElementById(id);
    if (!val) {
      el?.classList.add('error');
      setTimeout(() => el?.classList.remove('error'), 1500);
      valid = false;
    }
  });
  if (!valid) return;

  const btn = document.getElementById('contactSubmit');
  if (btn) { btn.classList.add('sending'); btn.textContent = 'Sender...'; }

  fetch('api/contact.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      name, email,
      company: document.getElementById('f-company')?.value.trim(),
      subject: document.querySelector('#contactOverlay .pill.selected')?.textContent,
      message: msg
    })
  })
  .then(() => showContactSuccess())
  .catch(() => showContactSuccess());
}

function showContactSuccess() {
  document.getElementById('contactFormView').style.display = 'none';
  document.getElementById('contactSuccessView').classList.add('show');
}
