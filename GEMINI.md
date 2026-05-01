# GEMINI.md — Project Context for Zacharias Polonius Portfolio

## Project Overview
This is a personal portfolio and professional resume website for **Zacharias Polonius**. It is built as a custom, lightweight CMS (Content Management System) using a decoupled architecture: a Vanilla JavaScript frontend and a PHP backend managing a JSON "database".

### Key Technologies
- **Frontend:** HTML5, CSS3 (Custom Properties, Grid, Flexbox), Vanilla JavaScript (ES6+).
- **Design System:** "Midnight Kinetic" — a custom dark/light mode system defined in `css/style.css`.
- **Backend:** PHP 7.4+ for authentication, file uploads, and data persistence.
- **Database:** `data/content.json` (Flat-file storage).

### Architecture
1.  **Data-Driven UI:** Almost all content (hero text, bento boxes, projects, CV entries, recommendations) is fetched dynamically from `content.json` using the Fetch API with cache-busting timestamps.
2.  **Admin Panel:** `admin.html` provides a comprehensive UI for managing the portfolio without touching code. It supports "Draft" modes, SEO alt-text management, and media uploads.
3.  **Secure API:** The `api/` directory handles sensitive operations.
    - `auth.php`: Shared session validation helper.
    - `login.php`/`logout.php`: Session-based authentication.
    - `save.php`: Atomic saving of the entire `content.json` with automatic backups (`.bak`).
    - `load.php`: Secure reading of data for the admin panel.
    - `upload.php`: Secure file handling for images and PDF documents.

## Directory Structure
- `/` : Public HTML pages.
- `/api` : PHP backend endpoints and `config.php` (ignored in git).
- `/assets` : Uploaded media (projects, photos, covers, docs).
- `/css` : Stylesheets, primarily `style.css`.
- `/data` : `content.json` database and `.htaccess` protection.
- `/js` : Frontend logic, primarily `main.js`.

## Development Conventions

### Coding Style
- **Vanilla over Frameworks:** Do not introduce heavy frameworks (React/Vue). Keep logic in clean, functional Vanilla JS.
- **Responsive First:** Use CSS Grid and Flexbox. Sitemaps must remain readable at 16px on mobile and 15px on desktop.
- **Midnight Kinetic System:** Adhere to defined CSS variables (e.g., `--blue`, `--bg`, `--border-md`) for consistency.

### API & Data Handling
- **Credentials:** Always use `credentials: 'same-origin'` in fetch calls to protected API endpoints.
- **Cache-Busting:** When fetching `content.json` in the frontend, append `?t=' + Date.now()` to ensure real-time updates.
- **Safe Slugs:** Use the `slug(str)` helper for generating IDs from titles.
- **Atomic Saves:** The admin panel sends the entire state back to `api/save.php`. Ensure the local `data` object is fully synchronized before calling `saveAll()`.

### Security
- **File Protection:** Directly accessing `.json` or sensitive `.php` files is blocked via `.htaccess` rules.
- **Session Management:** Cookies are configured with `HttpOnly`, `SameSite=Strict`, and dynamic `Secure` flags.
- **Input Sanitization:** Backend `upload.php` uses MIME-type verification (finfo) and safe filename generation.

## Key Procedures
- **Adding a new section:** 1. Update `content.json` structure. 2. Add renderer to `admin.html`. 3. Update frontend pages to fetch and display.
- **Changing Styles:** Modify `css/style.css`. Ensure both light and dark modes are tested.
- **Deployment:** Move files to an Apache-based server. Rename `htaccess.txt` to `.htaccess` in all directories if not already done.
