<?php
/**
 * api/config.example.php
 *
 * Omdøb denne fil til config.php og indsæt dine egne værdier.
 */

// Generer et nyt password-hash ved at køre dette i terminalen:
// php -r "echo password_hash('DIT_PASSWORD', PASSWORD_BCRYPT);"
define('ADMIN_PASSWORD_HASH', 'INDSÆT_DIT_HASH_HER');

// Session navn — gør det unikt
define('SESSION_NAME', 'zp_admin_sess');

// Session timeout i sekunder (8 timer)
define('SESSION_TIMEOUT', 28800);

// Sti til content.json
define('CONTENT_FILE', __DIR__ . '/../data/content.json');

// Gemini AI API Key (Hentes fra Google AI Studio)
define('GEMINI_API_KEY', 'INDSÆT_DIN_GEMINI_API_KEY_HER');

