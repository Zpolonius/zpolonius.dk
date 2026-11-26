/**
 * tests/track_locking_and_limits_test.mjs — issue #16
 * Kør: node tests/track_locking_and_limits_test.mjs   (exit 0 = alt bestået)
 *
 * Sikrer at api/track.php og api/log_ai_bot.php bruger fil-lås (flock + LOCK_EX),
 * ikke overskriver historik ved json_decode-fejl, og har rate-limit samt loft
 * over unique_visitors og referrers pr. dag.
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

const storePath = path.join(root, 'api', 'analytics_store.php');
const trackPhp = fs.readFileSync(path.join(root, 'api', 'track.php'), 'utf8');
const botPhp = fs.readFileSync(path.join(root, 'api', 'log_ai_bot.php'), 'utf8');
const storePhp = fs.existsSync(storePath) ? fs.readFileSync(storePath, 'utf8') : '';

// ---- 1. Fælles fil-lås og sikker JSON-håndtering ----
check(fs.existsSync(storePath), 'api/analytics_store.php findes');
check(
  storePhp.includes('flock(') && storePhp.includes('LOCK_EX') && storePhp.includes("'c+'"),
  'api/analytics_store.php åbner med c+ og låser med flock(..., LOCK_EX)'
);
check(
  storePhp.includes('json_last_error()') && storePhp.includes('ftruncate('),
  'api/analytics_store.php tjekker json_last_error() før ftruncate/skrivning'
);
check(
  trackPhp.includes('analytics_store.php') && trackPhp.includes('zp_update_analytics('),
  'api/track.php bruger zp_update_analytics() med fil-lås'
);
check(
  botPhp.includes('analytics_store.php') && botPhp.includes('zp_update_analytics('),
  'api/log_ai_bot.php bruger zp_update_analytics() med fil-lås'
);

// ---- 2. Rate limiting og lofter i api/track.php ----
check(
  trackPhp.includes('429') && trackPhp.includes('sys_get_temp_dir()'),
  'api/track.php har rate-limit pr. IP med HTTP 429'
);
check(
  /unique_visitors.*count\(|count\(.*unique_visitors/.test(trackPhp),
  'api/track.php begrænser antal unique_visitors pr. dag'
);
check(
  /referrers.*count\(|count\(.*referrers/.test(trackPhp),
  'api/track.php begrænser antal referrer-nøgler pr. dag'
);

console.log(fails ? `\n${fails} fejl` : '\nAlle tests bestået');
process.exit(fails ? 1 : 0);
