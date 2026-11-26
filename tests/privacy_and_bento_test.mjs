/**
 * tests/privacy_and_bento_test.mjs — issue #15 + ensartet bento-grid
 * Kør: node tests/privacy_and_bento_test.mjs   (exit 0 = alt bestået)
 */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.dirname(__dirname);

let fails = 0;
function check(cond, msg) {
  console.log((cond ? 'PASS ' : 'FAIL ') + msg);
  if (!cond) fails++;
}

// ---- 1. Issue #15: Ingen ukrypteret eller uautoriseret IP-overførsel til ip-api.com ----
const trackPhp = fs.readFileSync(path.join(root, 'api', 'track.php'), 'utf8');
check(
  !trackPhp.includes('ip-api.com'),
  'api/track.php sender ikke besøgendes IP til ip-api.com'
);
check(
  !/http:\/\/[^\s'"]*\{\$ip\}/.test(trackPhp),
  'api/track.php sender ikke rå $ip over ukrypteret HTTP'
);
check(
  trackPhp.includes("'locations' => []"),
  'api/track.php bevarer locations-nøglen i dagens datastruktur for bagudkompatibilitet'
);

// ---- 2. Bento-grid: Alle celler er lige store på desktop og mobil ----
const styleCss = fs.readFileSync(path.join(root, 'css', 'style.css'), 'utf8');
check(
  !/\.bento-grid\s+\.bento-featured\s*\{[^}]*grid-column\s*:\s*span\s+2/i.test(styleCss),
  'css/style.css spænder ikke .bento-featured over 2 kolonner'
);

console.log(fails ? `\n${fails} fejl` : '\nAlle tests bestået');
process.exit(fails ? 1 : 0);
