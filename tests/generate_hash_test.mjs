/**
 * tests/generate_hash_test.mjs — issue #17
 * Kør: node tests/generate_hash_test.mjs   (exit 0 = alt bestået)
 *
 * Sikrer at generate-hash.php ikke er tracked i git (med et hard-coded kodeord),
 * at ingen tracked .php-fil indeholder et hard-coded kodeord, og at
 * scripts/generate-hash.php er en CLI-only variant der læser kodeordet fra STDIN.
 */

import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.dirname(__dirname);

let fails = 0;
function check(cond, msg) {
  console.log((cond ? 'PASS ' : 'FAIL ') + msg);
  if (!cond) fails++;
}

const tracked = execFileSync('git', ['ls-files'], { cwd: root, encoding: 'utf8' })
  .split('\n')
  .map((s) => s.trim())
  .filter(Boolean);

// (a) generate-hash.php må ikke være tracked
check(
  !tracked.includes('generate-hash.php'),
  'generate-hash.php er ikke tracked i git'
);

// (b) ingen tracked .php-fil må indeholde et hard-coded kodeord-literal
const passwordLiteral = /\$password\s*=\s*(['"])(?!\1)[^'"\n]+\1/;
const phpFiles = tracked.filter((f) => f.toLowerCase().endsWith('.php'));
const offenders = phpFiles.filter((f) => {
  const src = fs.readFileSync(path.join(root, f), 'utf8');
  return passwordLiteral.test(src);
});
check(
  offenders.length === 0,
  `ingen tracked .php-fil har hard-coded \$password-literal (${offenders.length} fundet${offenders.length ? ': ' + offenders.join(', ') : ''})`
);

// (c) scripts/generate-hash.php: CLI-only, læser STDIN, bruger password_hash med BCRYPT
const scriptPath = path.join(root, 'scripts', 'generate-hash.php');
const exists = fs.existsSync(scriptPath);
check(exists, 'scripts/generate-hash.php findes');
const script = exists ? fs.readFileSync(scriptPath, 'utf8') : '';
check(/PHP_SAPI/.test(script) && /['"]cli['"]/.test(script), 'scripts/generate-hash.php tjekker PHP_SAPI === cli');
check(/STDIN/.test(script), 'scripts/generate-hash.php læser fra STDIN');
check(/password_hash\s*\(/.test(script) && /PASSWORD_BCRYPT/.test(script), 'scripts/generate-hash.php bruger password_hash med PASSWORD_BCRYPT');

console.log(fails ? `\n${fails} fejl` : '\nAlle tests bestået');
process.exit(fails ? 1 : 0);
