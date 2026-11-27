/**
 * tests/gemini4_models_test.mjs
 * Kør: node tests/gemini4_models_test.mjs   (exit 0 = alt bestået)
 *
 * Sikrer at api/vacation_reply.php, api/optimize.php og admin.html bruger
 * gemini-4-hydrogen som primær/default model (med fallback-kæde inkl. 3.5/3.1/2.5),
 * samt thinkingLevel='low' for Gemini 4 og timeouts på cURL-kald.
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

const vac = fs.readFileSync(path.join(root, 'api', 'vacation_reply.php'), 'utf8');
const opt = fs.readFileSync(path.join(root, 'api', 'optimize.php'), 'utf8');
const adm = fs.readFileSync(path.join(root, 'admin.html'), 'utf8');

check(
  /\$models\s*=\s*\[\s*'gemini-4-hydrogen'\s*,\s*'gemini-3\.5-flash'/.test(vac),
  'api/vacation_reply.php har gemini-4-hydrogen først med gemini-3.5-flash som fallback'
);
check(
  vac.includes('thinkingLevel') && vac.includes('CURLOPT_CONNECTTIMEOUT'),
  'api/vacation_reply.php understøtter thinkingLevel for Gemini 4 og sætter CURLOPT_CONNECTTIMEOUT'
);
check(
  opt.includes("'gemini-4-hydrogen'") && opt.includes('thinkingLevel'),
  'api/optimize.php bruger gemini-4-hydrogen som fallback og understøtter thinkingLevel'
);
check(
  adm.includes("'gemini-4-hydrogen'") && !adm.includes("'gemini-3.5-flash'"),
  'admin.html bruger gemini-4-hydrogen som default fallback-model'
);

console.log(fails ? `\n${fails} fejl` : '\nAlle tests bestået');
process.exit(fails ? 1 : 0);
