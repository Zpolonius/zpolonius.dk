# Upload-guide til Simply.com — tilgængelighed (roadmap N1, N2, N4)

Denne opdatering retter knapkontrast, footeren bag mobilnavigationen og
tilføjer et skip-link. Den retter også fejl fundet under testen:

- **Formularen på `/contact` kunne ikke sende beskeder.** Sidens script
  crashede, så beskeden blev sendt uden sikkerhedstoken og afvist af serveren.
- 404-siden kunne ikke scrolles på mobil.
- Ugyldig HTML (to `<main>` i hinanden) på detaljesiderne.

Ingen filer skal slettes. **`data/content.json` skal ikke røres.**

## Filer der skal uploades og overskrive de gamle

| Lokal fil | Læg i (på serveren) |
|---|---|
| `css/style.css` | mappen `css/` |
| `js/main.js` | mappen `js/` |
| `index.html` | webroden |
| `contact.html` | webroden |
| `vacation-reply.html` | webroden |
| `404.html` | webroden |
| `cv-print.html` | webroden |
| `about.php` | webroden |
| `cv.php` | webroden |
| `detail.php` | webroden |
| `insights.php` | webroden |
| `projects.php` | webroden |
| `recommendations.php` | webroden |

Upload **alle** filerne i samme omgang. Siderne henter nu
`style.css?v=1.1.0` og `main.js?v=1.1.0` — det tvinger browsere til at hente
de nye versioner (serveren cacher CSS og JS i et år).

## ⚠️ Rør ALDRIG disse på serveren

- ❌ `data/content.json`
- ❌ `data/analytics.json`
- ❌ `api/config.php`

## Test efter upload — 3 minutter

Lav en hard refresh (**Ctrl+F5**) først.

1. **Skip-link:** åbn forsiden, tryk **Tab** én gang. Øverst til venstre
   dukker "Spring til hovedindhold" op. Tryk **Enter**, og tryk **Tab** igen —
   fokus skal nu stå på "Book et gratis checkout-review", ikke i menuen.
2. **Knapfarve:** de blå knapper er en anelse dybere blå i mørkt tema.
   I lyst tema er farven den samme som før; kun hover-farven er ny.
3. **Footer på mobil:** scroll helt ned på telefonen. LinkedIn-, Instagram-
   og Facebook-ikonerne står frit over bundmenuen.
4. **Kontaktsiden (vigtigst):** gå til `/contact`, udfyld navn, e-mail,
   emne og besked. Fremdriftsbjælken skal nå helt ud. Send en testbesked til
   dig selv — du skal se "tak"-visningen og modtage **præcis én** mail.
5. **404:** åbn fx `zpolonius.dk/findes-ikke` på telefonen og scroll ned.
   Knapperne "Om mig / Projekter / CV / Kontakt" skal kunne nås.
