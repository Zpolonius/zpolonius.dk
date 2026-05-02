/* ============================================
   Z. POLONIUS — SHARED JAVASCRIPT
   ============================================ */

/* ---- UTILS ---- */
function esc(str) {
  if (!str) return '';
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {
  initSharedLayout();
  initTheme();
  initContactOverlay();
  initParallax();
});

/* ---- SHARED LAYOUT SYSTEM ---- */
function initSharedLayout() {
  const header = document.getElementById('global-header');
  const footer = document.getElementById('global-footer');
  const contact = document.getElementById('global-contact');
  const bottomNav = document.getElementById('global-bottom-nav');
  const floatingCta = document.getElementById('global-floating-cta');
  const ctaBar = document.getElementById('global-cta-bar');

  const navHtml = `
    <nav class="nav">
      <a href="index.html" class="nav-logo">Z<span>.</span>Polonius</a>
      <ul class="nav-links desktop-only">
        <li><a href="index.html">Hjem</a></li>
        <li><a href="projects.html">Projekter</a></li>
        <li><a href="index.html#blogSection">Indsigter</a></li>
        <li><a href="about.html">Om mig</a></li>
        <li><a href="cv.html">CV & Erfaring</a></li>
        <li><a href="recommendations.html">Anbefalinger</a></li>
        <li><a href="#" data-contact>Kontakt</a></li>
      </ul>
      <div class="nav-right">
        <ul class="nav-burger-links" id="burgerLinks">
          <li class="mobile-only"><a href="index.html">Hjem</a></li>
          <li class="mobile-only"><a href="projects.html">Projekter</a></li>
          <li class="mobile-only"><a href="index.html#blogSection">Indsigter</a></li>
          <li class="mobile-only"><a href="about.html">Om mig</a></li>
          <li class="mobile-only"><a href="cv.html">CV & Erfaring</a></li>
          <li class="mobile-only"><a href="recommendations.html">Anbefalinger</a></li>
          <li class="mobile-only"><a href="#" data-contact>Kontakt</a></li>
        </ul>
        <button class="theme-switch" onclick="toggleTheme()" title="Skift tema">☀</button>
        <button class="nav-btn desktop-only" data-contact>Book et review →</button>
      </div>
    </nav>
  `;

  const footerHtml = `
    <div class="footer-col footer-brand">
      <a href="index.html" class="nav-logo">Z<span>.</span>Polonius</a>
      <p class="footer-tagline">Bygger bro mellem kompleks teknologi og målbar forretningsværdi.</p>
      <div class="footer-left" style="margin-top: 24px;">
        Bjæverskov, Danmark<br>
        zacharias@polonius.dk · 3068 7041
      </div>
    </div>

    <div class="footer-col">
      <div class="footer-col-title">Sider</div>
      <ul class="footer-nav">
        <li><a href="index.html">Hjem</a></li>
        <li><a href="projects.html">Business Cases</a></li>
        <li><a href="index.html#blogSection">Indsigter</a></li>
        <li><a href="cv.html">CV & Erfaring</a></li>
        <li><a href="recommendations.html">Anbefalinger</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <div class="footer-col-title">Platform & Værktøjer</div>
      <div class="footer-credits">
        <p>Made with Claude, Gemini CLI and Antigravity.</p>
        <div class="credit-logos">
          <svg class="credit-logo" viewBox="0 0 24 24" fill="currentColor"><path d="m3.127 10.604 3.135-1.76.053-.153-.053-.085H6.11l-.525-.032-1.791-.048-1.554-.065-1.505-.08-.38-.081L0 7.832l.036-.234.32-.214.455.04 1.009.069 1.513.105 1.097.064 1.626.17h.259l.036-.105-.089-.065-.068-.064-1.566-1.062-1.695-1.121-.887-.646-.48-.327-.243-.306-.104-.67.435-.48.585.04.15.04.593.456 1.267.981 1.654 1.218.242.202.097-.068.012-.049-.109-.181-.9-1.626-.96-1.655-.428-.686-.113-.411a2 2 0 0 1-.068-.484l.496-.674L4.446 0l.662.089.279.242.411.94.666 1.48 1.033 2.014.302.597.162.553.06.17h.105v-.097l.085-1.134.157-1.392.154-1.792.052-.504.25-.605.497-.327.387.186.319.456-.045.294-.19 1.23-.37 1.93-.243 1.29h.142l.161-.16.654-.868 1.097-1.372.484-.545.565-.601.363-.287h.686l.505.751-.226.775-.707.895-.585.759-.839 1.13-.524.904.048.072.125-.012 1.897-.403 1.024-.186 1.223-.21.553.258.06.263-.218.536-1.307.323-1.533.307-2.284.54-.028.02.032.04 1.029.098.44.024h1.077l2.005.15.525.346.315.424-.053.323-.807.411-3.631-.863-.872-.218h-.12v.073l.726.71 1.331 1.202 1.667 1.55.084.383-.214.302-.226-.032-1.464-1.101-.565-.497-1.28-1.077h-.084v.113l.295.432 1.557 2.34.08.718-.112.234-.404.141-.444-.08-.911-1.28-.94-1.44-.759-1.291-.093.053-.448 4.821-.21.246-.484.186-.403-.307-.214-.496.214-.98.258-1.28.21-1.016.19-1.263.112-.42-.008-.028-.092.012-.953 1.307-1.448 1.957-1.146 1.227-.274.109-.477-.247.045-.44.266-.39 1.586-2.018.956-1.25.617-.723-.004-.105h-.036l-4.212 2.736-.75.096-.324-.302.04-.496.154-.162 1.267-.871z"/></svg>
          <svg class="credit-logo" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"/></svg>
          <svg class="credit-logo" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L4.5 20.29L5.21 21L12 18L18.79 21L19.5 20.29L12 2z"/></svg>
        </div>
        <p style="margin-top: 16px; font-size: 11px;">Designed with Google Stitch.<br>Vibed by Zacharias Bakahuge Polonius.</p>
      </div>
    </div>

    <div class="footer-col">
      <div class="footer-col-title">Social</div>
      <div class="footer-social">
        <a href="https://www.linkedin.com/in/zpolonius/" target="_blank" class="social-icon" title="LinkedIn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
        </a>
        <a href="https://www.instagram.com/zackp91/" target="_blank" class="social-icon" title="Instagram">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
        </a>
        <a href="https://www.facebook.com/zpolonius" target="_blank" class="social-icon" title="Facebook">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
        </a>
      </div>
    </div>
  `;

  const contactHtml = `
    <div class="overlay" id="contactOverlay">
      <div class="overlay-bg"></div>
      <div class="modal">
        <div id="contactFormView">
          <div class="modal-header">
            <div>
              <div class="modal-label">Kontakt</div>
              <div class="modal-title">Lad os tage<br>en snak</div>
              <div class="modal-sub">Jeg vender tilbage inden for 24 timer.</div>
            </div>
            <button class="modal-close" onclick="closeContact()">✕</button>
          </div>
          <div class="modal-progress"><div class="modal-progress-bar" id="contactProgressBar"></div></div>
          <div class="modal-divider"></div>
          <div class="modal-body">
            <div class="field-row">
              <div class="field"><label class="field-label">Navn</label><input class="input" type="text" id="f-name" placeholder="Dit fulde navn"></div>
              <div class="field"><label class="field-label">Firma</label><input class="input" type="text" id="f-company" placeholder="Virksomhedsnavn"></div>
            </div>
            <div class="field"><label class="field-label">Webshop URL (valgfrit)</label><input class="input" type="text" id="f-url" placeholder="https://dinshop.dk"></div>
            <div class="field"><label class="field-label">Email</label><input class="input" type="email" id="f-email" placeholder="din@email.dk"></div>
            <div class="field">
              <label class="field-label">Emne</label>
              <div class="pills" style="margin-top:4px;">
                <button class="pill">Jobmulighed</button>
                <button class="pill">Samarbejde</button>
                <button class="pill">AI-projekt</button>
                <button class="pill">Checkout review</button>
                <button class="pill">Andet</button>
              </div>
            </div>
            <div class="field" style="margin-top:18px;">
              <label class="field-label">Besked</label>
              <textarea class="input" id="f-msg" placeholder="Fortæl mig lidt om hvad du har i tankerne..."></textarea>
            </div>
            <div class="modal-footer">
              <div class="modal-contact-info">
                <span>zacharias@polonius.dk</span>
                <span>3068 7041</span>
              </div>
              <button class="btn-primary" id="contactSubmit">Send besked →</button>
            </div>
          </div>
          <div class="modal-social">
            <a href="https://www.linkedin.com/in/zpolonius/" target="_blank">LinkedIn</a>
            <span class="modal-social-sep">·</span>
            <a href="https://www.instagram.com/zackp91/" target="_blank">Instagram</a>
            <span class="modal-social-sep">·</span>
            <a href="https://www.facebook.com/zpolonius" target="_blank">Facebook</a>
          </div>
        </div>
        <div class="success-view" id="contactSuccessView">
          <div class="success-icon">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
          </div>
          <div class="success-title">Besked sendt!</div>
          <div class="success-sub">Tak for din henvendelse. Jeg vender tilbage inden for 24 timer.</div>
          <button class="btn-primary" onclick="closeContact()" style="margin-top:8px;">Luk vindue</button>
        </div>
      </div>
    </div>
  `;

  const bottomNavHtml = `
    <nav class="bottom-nav" id="bottomNav">
      <a class="bottom-nav-item" href="index.html" data-page="hjem">
        <span class="bottom-nav-icon">⌂</span>
        <span class="bottom-nav-label">Hjem</span>
      </a>
      <div class="bottom-nav-divider"></div>
      <a class="bottom-nav-item" href="projects.html" data-page="projekter">
        <span class="bottom-nav-icon">◈</span>
        <span class="bottom-nav-label">Projekter</span>
      </a>
      <div class="bottom-nav-divider"></div>
      <a class="bottom-nav-item" href="index.html#blogSection" data-page="indsigter">
        <span class="bottom-nav-icon">📝</span>
        <span class="bottom-nav-label">Indsigter</span>
      </a>
      <div class="bottom-nav-divider"></div>
      <button class="bottom-nav-contact" data-contact-mobile>
        <span class="bottom-nav-icon">✉</span>
        <span class="bottom-nav-label">Kontakt</span>
      </button>
      <div class="bottom-nav-divider"></div>
      <button class="bottom-nav-item" id="mobileBurgerBtn">
        <span class="bottom-nav-icon">☰</span>
        <span class="bottom-nav-label">Mere</span>
      </button>
    </nav>
  `;

  const floatingCtaHtml = `
    <button class="floating-cta" data-contact>
      <span class="icon">✉</span>
      Kontakt
    </button>
  `;

  const ctaBarHtml = `
    <div class="cta-bar-central">
      <div class="cta-bar-content">
        <p class="cta-bar-title">Klar til at optimere jeres digitale setup?</p>
        <p class="cta-bar-sub">Lad os tage en uforpligtende snak om jeres forretningsmål.</p>
      </div>
      <button class="btn-primary" data-contact>Book et review →</button>
    </div>
  `;

  if (header) header.innerHTML = navHtml;
  if (footer) footer.innerHTML = footerHtml;
  if (contact) contact.innerHTML = contactHtml;
  if (bottomNav) bottomNav.innerHTML = bottomNavHtml;
  if (floatingCta) floatingCta.innerHTML = floatingCtaHtml;
  if (ctaBar) ctaBar.innerHTML = ctaBarHtml;

  // Re-init interactive parts
  initHamburger();
  initBottomNav();
  initActiveNav();
  initContactOverlay();
}

/* ---- TYPEWRITER ---- */
window.initTypewriter = function(element, words) {
  let wordIndex = 0;
  let charIndex = 0;
  let isDeleting = false;

  function type() {
    const currentWord = words[wordIndex];
    if (isDeleting) {
      charIndex--;
    } else {
      charIndex++;
    }

    element.textContent = currentWord.substring(0, charIndex);

    let typeSpeed = isDeleting ? 40 : 80;

    if (!isDeleting && charIndex === currentWord.length) {
      typeSpeed = 2000;
      isDeleting = true;
    } else if (isDeleting && charIndex === 0) {
      isDeleting = false;
      wordIndex = (wordIndex + 1) % words.length;
      typeSpeed = 500;
    }

    setTimeout(type, typeSpeed);
  }
  
  type();
};

/* ---- PARALLAX ---- */
function initParallax() {
  const heroImg = document.querySelector('.hero-img');
  if (!heroImg) return;
  
  window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    if (scrolled < 800) {
      heroImg.style.transform = `translateY(${scrolled * 0.4}px) scale(1.05)`;
    }
  });
}

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
  document.querySelectorAll('.nav-links a, .nav-burger-links a').forEach(a => {
    if (a.getAttribute('href') === page) a.classList.add('active');
  });
}

/* ---- HAMBURGER & BURGER MENU ---- */
function initHamburger() {
  const burgerLinks = document.getElementById('burgerLinks');
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const mobileBurgerBtn = document.getElementById('mobileBurgerBtn');

  if (!burgerLinks) return;

  function toggleMenu() {
    const isOpen = burgerLinks.classList.toggle('open');
    if (hamburgerBtn) hamburgerBtn.setAttribute('aria-expanded', isOpen);
    if (mobileBurgerBtn) mobileBurgerBtn.classList.toggle('active', isOpen);
  }

  hamburgerBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    toggleMenu();
  });

  mobileBurgerBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    toggleMenu();
  });

  // Close menu when clicking outside
  document.addEventListener('click', (e) => {
    if (burgerLinks.classList.contains('open')) {
      if (!burgerLinks.contains(e.target) && 
          !hamburgerBtn?.contains(e.target) && 
          !mobileBurgerBtn?.contains(e.target)) {
        burgerLinks.classList.remove('open');
        hamburgerBtn?.setAttribute('aria-expanded', false);
        mobileBurgerBtn?.classList.remove('active');
      }
    }
  });

  // Close menu when clicking a link
  burgerLinks.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => {
      burgerLinks.classList.remove('open');
      hamburgerBtn?.setAttribute('aria-expanded', false);
      mobileBurgerBtn?.classList.remove('active');
    });
  });
}

/* ---- FLOATING BOTTOM NAV ---- */
function initBottomNav() {
  const nav = document.getElementById('bottomNav');
  if (!nav) return;

  const page = location.pathname.split('/').pop() || 'index.html';
  const map = {
    'index.html':           'hjem',
    '':                     'hjem',
    'projects.html':        'projekter',
    'cv.html':              'cv',
    'recommendations.html': 'anbefalinger',
    'about.html':           'om',
    'contact.html':         'kontakt',
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
  ['f-name','f-email','f-company','f-url','f-msg'].forEach(id => {
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
      url: document.getElementById('f-url')?.value.trim(),
      subject: document.querySelector('#contactOverlay .pill.selected')?.textContent,
      message: msg
    })
  })
  .then(r => r.json())
  .then(res => {
    if (res.ok) {
      showContactSuccess();
    } else {
      btn.classList.remove('sending');
      btn.textContent = 'Send besked →';
      alert('Fejl: ' + (res.error || 'Kunne ikke sende besked'));
    }
  })
  .catch(() => {
    btn.classList.remove('sending');
    btn.textContent = 'Send besked →';
    alert('Der opstod en netværksfejl. Prøv venligst igen.');
  });
}

function showContactSuccess() {
  document.getElementById('contactFormView').style.display = 'none';
  document.getElementById('contactSuccessView').classList.add('show');
}


/* ---- HTML TYPEWRITER ---- */
window.typeHTML = function(element, htmlString, speed = 8) {
  element.innerHTML = '';
  const tempDiv = document.createElement('div');
  tempDiv.innerHTML = htmlString;
  
  let skip = false;
  const skipHandler = () => { skip = true; };
  // Bind to any click in the document to skip typing
  document.addEventListener('click', skipHandler, { once: true });

  function typeNode(node, targetNode, callback) {
    if (node.nodeType === Node.TEXT_NODE) {
      let text = node.textContent;
      let i = 0;
      function typeChar() {
        if (skip) {
          targetNode.textContent += text.substring(i);
          callback();
          return;
        }
        if (i < text.length) {
          targetNode.textContent += text.charAt(i);
          i++;
          setTimeout(typeChar, speed);
        } else {
          callback();
        }
      }
      typeChar();
    } else if (node.nodeType === Node.ELEMENT_NODE) {
      const newElem = document.createElement(node.tagName);
      for (let attr of node.attributes) {
        newElem.setAttribute(attr.name, attr.value);
      }
      targetNode.appendChild(newElem);
      
      let childNodes = Array.from(node.childNodes);
      let childIndex = 0;
      function processNextChild() {
        if (childIndex < childNodes.length) {
          typeNode(childNodes[childIndex], newElem, () => {
            childIndex++;
            processNextChild();
          });
        } else {
          callback();
        }
      }
      processNextChild();
    } else {
      callback();
    }
  }

  let rootChildNodes = Array.from(tempDiv.childNodes);
  let rootIndex = 0;
  function processNextRootChild() {
    if (rootIndex < rootChildNodes.length) {
      typeNode(rootChildNodes[rootIndex], element, () => {
        rootIndex++;
        processNextRootChild();
      });
    } else {
      document.removeEventListener('click', skipHandler);
    }
  }
  processNextRootChild();
};
