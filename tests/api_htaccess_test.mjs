/**
 * tests/api_htaccess_test.mjs — issue #18
 * Kør: node tests/api_htaccess_test.mjs   (exit 0 = alt bestået)
 *
 * Sikrer at api/.htaccess ikke ophæver sine egne blokeringer af config.php og auth.php
 * med en senere Allow-regel, og at backup-/editor-varianter af config.php også blokeres.
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

const ht = fs.readFileSync(path.join(root, 'api', '.htaccess'), 'utf8');

check(
  !/<FilesMatch\s+"\\\.php\$"[\s\S]*?Allow from all/i.test(ht),
  'api/.htaccess har ikke en <FilesMatch "\\.php$"> Allow from all-blok der ophæver blokeringer'
);
check(
  !/Allow from all|Require all granted/i.test(ht),
  'api/.htaccess indeholder ingen Allow from all / Require all granted der kan overskrive Deny'
);
check(
  /<FilesMatch\s+"\^?\(?config\\\.php/i.test(ht),
  'api/.htaccess blokerer config.php og backup-varianter (config.php*) via FilesMatch'
);
check(
  /auth\\\.php/i.test(ht) && /Require all denied/i.test(ht) && /Deny from all/i.test(ht),
  'api/.htaccess blokerer auth.php med både Apache 2.4 (Require all denied) og 2.2 (Deny from all)'
);

console.log(fails ? `\n${fails} fejl` : '\nAlle tests bestået');
process.exit(fails ? 1 : 0);
