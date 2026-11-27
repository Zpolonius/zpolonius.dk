/**
 * spor.js — spor-felt (Webshop / Leder / Nysgerrig) på projekter, artikler og anbefalinger.
 * Ren logik uden DOM: virker både i browseren (window.Spor) og i node (module.exports).
 * Ingen valgte spor = "alle spor": nøglen `spor` udelades helt fra objektet.
 */
(function (root) {
  'use strict';

  var SPOR = ['shop', 'hire', 'curious'];
  var LABELS = { shop: 'Webshop', hire: 'Leder', curious: 'Nysgerrig' };

  // Filtrer til kendte værdier, fjern dubletter, fast rækkefølge. Alt andet giver [].
  function normalizeSpor(arr) {
    if (!Array.isArray(arr)) return [];
    return SPOR.filter(function (s) { return arr.indexOf(s) !== -1; });
  }

  // Sæt obj.spor hvis noget er valgt, ellers fjern nøglen. Returnerer obj.
  function applySpor(obj, selected) {
    var n = normalizeSpor(selected);
    if (n.length) obj.spor = n;
    else delete obj.spor;
    return obj;
  }

  // Item uden (eller med tomt) spor tæller som alle spor.
  function matchesSpor(item, spor) {
    var n = normalizeSpor(item && item.spor);
    return n.length === 0 || n.indexOf(spor) !== -1;
  }

  var api = { SPOR: SPOR, LABELS: LABELS, normalizeSpor: normalizeSpor, applySpor: applySpor, matchesSpor: matchesSpor };
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
  if (root) root.Spor = api;
})(typeof window !== 'undefined' ? window : null);
