# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Personal portfolio and lightweight CMS for Zacharias Polonius. Zero-framework philosophy: Vanilla JS frontend + PHP 7.4+ backend with `data/content.json` as a flat-file database. No Node.js, no build step, no package manager.

## Dev Environment

Requires Apache with `.htaccess` enabled and PHP 7.4+. Local dev via XAMPP or MAMP.

**First-time setup:**
```bash
cp api/config.example.php api/config.php
# Set admin password hash and credentials in api/config.php
# Ensure data/ and assets/ are writable by the web server
```

No build, lint, or test commands exist — this project runs directly from source.

## Architecture

Three layers:

1. **Presentation** — HTML pages (`index.html`, `projects.html`, `cv.html`, `about.html`, `contact.html`, `insights.html`, `recommendations.html`, `detail.html`) fetch `data/content.json` and render dynamically. No server-side templating.
2. **Admin** — `admin.html` (~125KB) is a full CMS UI using [Quill](https://quilljs.com/) for rich text. It manages all content, media uploads, SEO alt-text, and draft visibility without touching code.
3. **Data API** — PHP endpoints in `/api/` handle CRUD on `content.json`, session auth, file uploads, contact form, and analytics tracking.

**Shared frontend bootstrap** (`js/main.js`): On every page, `DOMContentLoaded` triggers `loadContentData()` → fetch `content.json` → `initSharedLayout()` (nav/footer) → page-specific renderers → theme, analytics, cookie consent, parallax, transitions.

## Content Data Model

`data/content.json` is the single source of truth. Top-level keys: `site`, `hero`, `specialer`, `bento`, `om`, `projects`, `cv` (contains `jobs`, `education`, `recommendations`). The admin panel saves the entire object atomically via `api/save.php`, which also writes a `.bak` backup.

## Coding Conventions (from GEMINI.md)

- **No frameworks:** Keep logic in clean, functional Vanilla JS. Do not introduce React/Vue.
- **Design System:** Use existing CSS variables (`--blue`, `--bg`, `--border-md`, etc.) from `css/style.css`. Both light and dark modes must be tested when changing styles.
- **Fetch calls to API:** Always include `credentials: 'same-origin'`.
- **Fetching content.json:** Append `?t=' + Date.now()` for cache-busting.
- **ID generation:** Use the `slug(str)` helper in `js/main.js` for consistent slugs.
- **Atomic saves:** The admin panel syncs the entire local `data` object before calling `saveAll()`. Never partially update `content.json`.

## Adding a New Section

1. Update the `content.json` structure (and the admin panel renderer in `admin.html`).
2. Add a renderer to the relevant frontend page(s).
3. Expose a management UI in `admin.html`.

## Security Notes

- `api/config.php` is git-ignored — never commit it.
- `.htaccess` files block direct access to `.json`, `.bak`, `config.php`, and `auth.php`.
- Auth: bcrypt password in `config.php`, session cookies with `HttpOnly`/`SameSite=Strict`/`Secure`, 8-hour timeout, rate-limited login (10 attempts / 15 min per IP).
- File uploads: MIME-type verified via `finfo`; safe filenames generated server-side.

## Deployment

Copy all files to an Apache server root. Ensure `.htaccess` is active in `/`, `/api/`, and `/data/`. If `.htaccess` files were renamed to `htaccess.txt` during transfer, rename them back.
