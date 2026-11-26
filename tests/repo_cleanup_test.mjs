/**
 * tests/repo_cleanup_test.mjs — issue #21
 * Kør: node tests/repo_cleanup_test.mjs   (exit 0 = alt bestået)
 *
 * Sikrer at api/db-test.php og mellemfiler i data/ ikke findes i repoet,
 * samt at .gitignore ignorerer fremtidige mellemfiler i data/.
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

check(!fs.existsSync(path.join(root, 'api', 'db-test.php')), 'api/db-test.php er fjernet');

const dataFiles = fs.readdirSync(path.join(root, 'data')).sort();
check(
  JSON.stringify(dataFiles) === JSON.stringify(['.htaccess', 'content.json']),
  `data/ indeholder kun .htaccess og content.json (${dataFiles.join(', ')})`
);

const gitignore = fs.readFileSync(path.join(root, '.gitignore'), 'utf8');
for (const pattern of ['data/*.bak*', 'data/content.*.json', 'data/content (*).json']) {
  check(gitignore.includes(pattern), `.gitignore indeholder ${pattern}`);
}

console.log(fails ? `\n${fails} fejl` : '\nAlle tests bestået');
process.exit(fails ? 1 : 0);
