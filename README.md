# 🌌 Midnight Kinetic — Portfolio & Custom CMS

> **Dansk:** Et højtydende, specialbygget portefølje-system og letvægts-CMS designet til Zacharias Polonius. Dette projekt fremviser en afkoblet arkitektur, der kombinerer enkelheden ved en fladfil-database med kraften fra en moderne, "kinetisk" brugerflade.

![Portfolio Cover](assets/cover.webp)

A high-performance, custom-built portfolio engine and lightweight CMS designed for **Zacharias Polonius**. This project showcases a decoupled architecture combining the simplicity of a flat-file database with the power of a modern, "kinetic" user interface.

[Live Demo](https://zpolonius.dk) · [Report Bug](https://github.com/zpolonius/zpoloniusdk/issues) · [Request Feature](https://github.com/zpolonius/zpoloniusdk/issues)

---

## ✨ Features

- **🚀 Decoupled Architecture:** A snappy Vanilla JS frontend paired with a secure, atomic PHP backend.
- **🎨 Midnight Kinetic Design System:** A custom-engineered CSS system supporting real-time Light/Dark mode transitions, glassmorphism, and high-tactile feedback.
- **🍱 Dynamic Bento Grid:** An interactive, responsive grid system for showcasing key metrics and highlights.
- **🛠️ Zero-Framework CMS:** A built-in admin panel (`admin.html`) to manage projects, CV entries, and testimonials without touching a line of code.
- **🔒 Security First:** 
  - Session-based authentication with `HttpOnly` and `SameSite` cookies.
  - CSRF protection and input sanitization.
  - Automatic atomic backups (`.bak`) for every data change.
- **⚡ Performance Optimized:** Pre-loaded assets, lazy loading, and efficient JSON fetching with cache-busting.

---

## 🛠️ Tech Stack

### Frontend
- **HTML5 & CSS3:** Utilizes CSS Grid, Flexbox, and Custom Properties (Variables).
- **Vanilla JavaScript (ES6+):** Functional programming patterns, Fetch API, and dynamic rendering.
- **Design:** "Midnight Kinetic" — focused on depth, typography, and motion.

### Backend
- **PHP 7.4+:** Handles authentication, file uploads, and data persistence.
- **JSON Engine:** Uses `data/content.json` as a lightning-fast flat-file database.
- **Security:** `.htaccess` rules for folder protection and secure MIME-type verification for uploads.

---

## 🏗️ Architecture

The project is split into three main layers:

1.  **Presentation Layer:** Dynamic pages (`index.html`, `projects.html`, `cv.html`) that fetch and render content in real-time.
2.  **Management Layer:** A full-featured Admin UI for content creation, SEO management (Alt-text), and media management.
3.  **Data API:** Secure PHP endpoints (`/api`) that handle all CRUD operations on the JSON database.

---

## 🚀 Getting Started

### Prerequisites
- An Apache-based web server (XAMPP, MAMP, or a live Linux server).
- PHP 7.4 or higher.

### Installation
1. **Clone the repository:**
   ```bash
   git clone https://github.com/zpolonius/zpoloniusdk.git
   ```
2. **Setup the Backend:**
   - Copy `api/config.example.php` to `api/config.php`.
   - Set your secure admin password/credentials in `api/config.php`.
3. **Permissions:**
   - Ensure the `data/` and `assets/` folders are writable by the web server.
4. **Deploy:**
   - Move the files to your server's root directory.
   - Ensure `.htaccess` is enabled to protect the `data/` folder.

---

## 🎨 Design Principles

> *"Small details lead to big changes."*

The **Midnight Kinetic** system is built on:
- **Depth:** Multi-layered drop shadows and glassmorphism.
- **Tactility:** Subtle noise textures and responsive hover states.
- **Typography:** Focused on readability using the 'Inter' typeface with optimized line heights.
- **Color:** A vibrant primary blue (#418FFF) against deep, obsidian backgrounds.

---

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

---

## 🤝 Contact

Zacharias Polonius - [@zachariaspolonius](https://www.linkedin.com/in/zpolonius/) - zacharias@polonius.dk

Project Link: [https://github.com/zpolonius/zpolonius.dk](https://github.com/Zpolonius/zpolonius.dk)
