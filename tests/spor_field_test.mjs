/**
 * tests/spor_field_test.mjs — issue #22
 * Kør: node tests/spor_field_test.mjs   (exit 0 = alt bestået)
 *
 * Tester js/spor.js (ren logik) og at admin.html viser/gemmer spor-feltet
 * i projekt-, artikel- og anbefalings-editoren.
 */
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import assert from 'node:assert';
import { fileURLToPath } from 'node:url';

const root = path.dirname(path.dirname(fileURLToPath(import.meta.url)));
let fails = 0;
function check(name, fn) {
  try { fn(); console.log('PASS ' + name); }
  catch (e) { console.log('FAIL ' + name + ' — ' + e.message.split('\n')[0]); fails++; }
}

const sporPath = path.join(root, 'js', 'spor.js');
let Spor = null;
check('js/spor.js findes og eksporterer Spor', () => {
  assert.ok(fs.existsSync(sporPath), 'js/spor.js mangler');
  const ctx = { window: {} };
  vm.runInNewContext(fs.readFileSync(sporPath, 'utf8'), ctx);
  Spor = ctx.window.Spor;
  assert.ok(Spor, 'window.Spor mangler');
});

if (Spor) {
  check('SPOR og labels', () => {
    assert.deepStrictEqual([...Spor.SPOR], ['shop', 'hire', 'curious']);
    assert.deepStrictEqual({ ...Spor.LABELS }, { shop: 'Webshop', hire: 'Leder', curious: 'Nysgerrig' });
  });
  check('normalizeSpor: kanten', () => {
    const n = Spor.normalizeSpor;
    for (const v of [null, undefined, 'shop', 42, {}, [], ['x', 1, null]]) assert.deepStrictEqual([...n(v)], []);
    assert.deepStrictEqual([...n(['curious', 'shop'])], ['shop', 'curious']);
    assert.deepStrictEqual([...n(['hire', 'hire', 'bogus', 'shop', 'hire'])], ['shop', 'hire']);
    assert.deepStrictEqual([...n(['curious', 'hire', 'shop'])], ['shop', 'hire', 'curious']);
  });
  check('applySpor uden valg fjerner nøglen og bevarer rækkefølge', () => {
    const orig = { id: 'a', title: 'T', visible: true, body: '<p>x</p>' };
    const before = JSON.stringify(orig);
    const out = Spor.applySpor({ ...orig }, []);
    assert.deepStrictEqual(out, orig);
    assert.strictEqual(JSON.stringify(out), before);
    assert.ok(!('spor' in out));
    const had = Spor.applySpor({ id: 'a', spor: ['shop'], z: 1 }, []);
    assert.ok(!('spor' in had));
    assert.deepStrictEqual(Object.keys(had), ['id', 'z']);
    assert.ok(!('spor' in Spor.applySpor({ id: 'a' }, null)));
  });
  check('applySpor med valg', () => {
    const o = { id: 'a' };
    assert.strictEqual(Spor.applySpor(o, ['curious', 'shop']), o);
    assert.deepStrictEqual([...o.spor], ['shop', 'curious']);
    assert.deepStrictEqual([...Spor.applySpor({ id: 'b' }, ['bogus', 'hire']).spor], ['hire']);
  });
  check('matchesSpor', () => {
    for (const s of ['shop', 'hire', 'curious']) {
      assert.strictEqual(Spor.matchesSpor({ id: 'a' }, s), true);
      assert.strictEqual(Spor.matchesSpor({ id: 'a', spor: [] }, s), true);
    }
    const it = { spor: ['shop', 'curious'] };
    assert.strictEqual(Spor.matchesSpor(it, 'shop'), true);
    assert.strictEqual(Spor.matchesSpor(it, 'curious'), true);
    assert.strictEqual(Spor.matchesSpor(it, 'hire'), false);
  });
}

const admin = fs.readFileSync(path.join(root, 'admin.html'), 'utf8');
check('admin.html inkluderer js/spor.js før hovedscriptet', () => {
  const i = admin.indexOf('<script src="js/spor.js?v=1"></script>');
  assert.ok(i > -1, 'script-tag mangler');
  assert.ok(i < admin.indexOf('function openProjectForm'), 'skal stå før inline-scriptet');
});
check('fieldset-helper findes med legend', () => {
  assert.ok(/function\s+sporFieldset\s*\(/.test(admin), 'sporFieldset mangler');
  assert.ok(admin.includes('<fieldset') && admin.includes('<legend'), 'fieldset/legend mangler');
  assert.ok(admin.includes('Vis i spor'), 'legend-tekst mangler');
});
function body(fnName, next) {
  const a = admin.indexOf('function ' + fnName);
  const b = admin.indexOf(next, a);
  return a > -1 && b > a ? admin.slice(a, b) : '';
}
for (const [fn, next, prefix] of [
  ['openProjectForm', '/* ---- ANSÆTTELSER', 'pj'],
  ['openRecForm', '/* ---- OM MIG', 'rc'],
  ['openArticleForm', '/* ---- INITIALIZATION', 'ar'],
]) {
  check(`${prefix}: formular rendrer fieldset og gem læser/anvender spor`, () => {
    const src = body(fn, next);
    assert.ok(src, fn + ' ikke fundet');
    assert.ok(src.includes(`sporFieldset('${prefix}'`), 'kalder ikke sporFieldset');
    assert.ok(src.includes(`readSpor('${prefix}')`) && src.includes('Spor.applySpor('), 'kalder ikke readSpor + Spor.applySpor');
  });
}

console.log(fails ? `\n${fails} fejl` : '\nAlle tests bestået');
process.exit(fails ? 1 : 0);
