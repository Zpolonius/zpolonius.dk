# assets/figure.webp — hero-portræt

Fritlagt portræt brugt i forsidens hero (`index.html`, `.hero-figure`).
Formålet med noten er at kunne gentage behandlingen, når billedet skal
udskiftes, så version to ser ud som version et.

Felter markeret **UKENDT** kunne ikke aflæses af filen eller repoet og skal
udfyldes af den der lavede billedet.

## Verificeret fra filen (30-09-2026)

| Egenskab | Værdi |
|---|---|
| Dimensioner | 818 × 1150 px (portræt, ca. 0,71:1) |
| Format | WebP, udvidet (VP8X) |
| Farvedata | Lossy (VP8) |
| Alfakanal | Ja, lossless-komprimeret (ALPH, filter 3) |
| Farveprofil / EXIF / XMP | Ingen — antag sRGB |
| Filstørrelse | 86.922 bytes (~87 KB) |
| Tilføjet | Commit `529f1b4`, 19-09-2026 |

## Behandlingstrin

| Trin | Værdi |
|---|---|
| Kildefil (navn, opløsning, hvor den ligger) | **UKENDT** |
| Fritlægningsmodel / værktøj | **UKENDT** |
| Beskæring (udsnit i kildefilen) | **UKENDT** |
| Alfa-erodering (px / metode) | **UKENDT** |
| Tonning (værdier) | **UKENDT** |
| WebP-eksport (værktøj, kvalitet, alfa-kvalitet) | **UKENDT** — farvedata er lossy, alfa er lossless |

## Krav til et nyt billede

Hero-CSS'en i `index.html` er afstemt efter dette billede. Et nyt portræt
bør derfor holde:

- **Gennemsigtig baggrund.** Uden alfa får hero'en en firkantet kasse.
- **Samme proportioner (ca. 818 × 1150).** Figuren skaleres på højden
  (`height: 116%` på desktop), så et bredere billede rykker ind over teksten.
- **Opdater `width`/`height` på `<img class="hero-figure">`** hvis
  dimensionerne ændres — de forhindrer layoutskift ved indlæsning.
- **Busten skal gå ud af billedets underkant.** Masken i CSS'en fader de
  nederste ~25% ud, så en hård underkant forsvinder.
- **Lyst tema** lægger `filter: saturate(1.08) contrast(1.02)` på figuren.
  Tonning skal derfor ikke bages ind specifikt til ét tema.

Billedet kan også skiftes fra admin (Forside → Hero → Cover), men den gamle
`assets/cover.webp` ignoreres bevidst af koden (se `index.html`).
