/**
 * tests/php85_curl_close_test.mjs
 * Kør: node tests/php85_curl_close_test.mjs   (exit 0 = alt bestået)
 *
 * Sikrer at ingen PHP-filer i api/ kalder curl_close(), som er deprecated i PHP 8.5
 * og kan udskrive HTML-advarsler foran JSON-output.
 */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.dirname(__dirname);
const apiDir = path.join(root, 'api');

let fails = 0;
function check(cond, msg) {
  console.log((cond ? 'PASS ' : 'FAIL ') + msg);
  if (!cond) fails++;
}

for (const file of fs.readdirSync(apiDir)) {
  if (!file.endsWith('.php')) continue;
  const src = fs.readFileSync(path.join(apiDir, file), 'utf8');
  check(!/\bcurl_close\s*\(/.test(src), `api/${file} bruger ikke deprecated curl_close()`);
}

console.log(fails ? `\n${fails} fejl` : '\nAlle tests bestået');
process.exit(fails ? 1 : 0);
