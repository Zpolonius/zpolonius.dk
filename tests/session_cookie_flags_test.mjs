/**
 * tests/session_cookie_flags_test.mjs — issue #20
 * Kør: node tests/session_cookie_flags_test.mjs   (exit 0 = alt bestået)
 *
 * Sikrer at api/csrf.php, api/contact.php, api/vacation_reply.php og api/auth.php
 * bruger en fælles start_session()-helper med HttpOnly, Secure og SameSite=Strict.
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

const sessionPath = path.join(root, 'api', 'session.php');
const sessionPhp = fs.existsSync(sessionPath) ? fs.readFileSync(sessionPath, 'utf8') : '';

check(fs.existsSync(sessionPath), 'api/session.php findes');
check(
  sessionPhp.includes('function start_session(') &&
    sessionPhp.includes("ini_set('session.cookie_httponly', 1)") &&
    sessionPhp.includes("ini_set('session.cookie_secure'") &&
    sessionPhp.includes("ini_set('session.cookie_samesite', 'Strict')"),
  'api/session.php definerer start_session() med HttpOnly, Secure og SameSite=Strict'
);

for (const file of ['api/auth.php', 'api/csrf.php', 'api/contact.php', 'api/vacation_reply.php']) {
  const src = fs.readFileSync(path.join(root, file), 'utf8');
  check(
    src.includes('session.php') && src.includes('start_session(') && !/\bsession_start\s*\(/.test(src),
    `${file} bruger start_session() i stedet for direkte session_start()`
  );
}

console.log(fails ? `\n${fails} fejl` : '\nAlle tests bestået');
process.exit(fails ? 1 : 0);
