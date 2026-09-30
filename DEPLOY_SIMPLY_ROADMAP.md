# Upload-guide til Simply.com — roadmap-beslutningerne

Denne opdatering bygger dine beslutninger fra roadmappen:

- **Ét navn på alle kontaktknapper:** "Tjek min checkout" (X2, N6)
- **Én knap ad gangen** på skærmen (X1)
- **"Kort fortalt"** som synlig overskrift over profilkortene (N3)
- **Kortfarver:** blå = fag, grøn = menneske (X4)
- **Tre fremhævede kort** (X5)
- **Kortere cookiebanner** med "Læs mere" (X3)
- **Alle små versal-labels i 11px** (S1)
- **Klikbar e-mail og telefon** i footeren (S2)

Upload først PR'en med tilgængeligheds-rettelserne (`DEPLOY_SIMPLY_A11Y.md`),
hvis den ikke allerede er oppe. Denne bygger ovenpå.

## 1) Upload og overskriv disse filer

| Lokal fil | Læg i (på serveren) |
|---|---|
| `.htaccess` | webroden — **se note nedenfor** |
| `admin.html` | webroden |
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

**Note om `.htaccess`:** filen starter med et punktum og er derfor skjult i
mange FTP-programmer. Slå "vis skjulte filer" til. Ændringen gør, at dine
sider ikke længere gemmes i en måned i besøgendes browser — så de ser nye
ændringer med det samme næste gang.

## ⚠️ Rør ALDRIG disse på serveren

- ❌ `data/content.json` — heller ikke selvom den er ændret i repoet
- ❌ `data/analytics.json`
- ❌ `api/config.php`

## 2) Sæt farver og fremhævning i admin (vigtigt)

Farverne og de fremhævede kort ligger i dit indhold, ikke i koden. Da
`content.json` ikke må uploades, skal du sætte dem i admin:

1. Log ind på `zpolonius.dk/admin` → **Forside**.
2. Tryk **Rediger** ud for hvert kort og sæt:

| Kort | Accent-farve | Fremhæv boksen |
|---|---|---|
| Mellem kode og krone | Blå | ✅ |
| Jeg siger det ingen andre tør | Blå | – |
| Din checkout fortæller en historie | Blå | ✅ |
| Mennesket før løsningen | Grøn | – |
| AI er ikke fremtiden | Blå | ✅ |
| Familiefar fra Bjæverskov | Grøn | – |

3. Tryk **Gem** for hvert kort. Listen viser "★ Fremhævet" ved de tre.

## 3) Test efter upload — 3 minutter

Lav en hard refresh (**Ctrl+F5**) først.

1. **Forsiden på computer:** kun den blå "Tjek min checkout →" i hero'en er
   synlig — ikke også i menuen. Scroll ned: den flydende knap nederst til
   højre dukker først op, når hero'en er forbi.
2. **Profilkort:** "Kort fortalt" står over kortene. Tre kort er dobbelt så
   brede med blå kant.
3. **Cookiebanner:** åbn siden i et privat vindue på telefonen. Banneret
   fylder ca. en femtedel af skærmen og dækker ikke bundmenuen. "Læs mere"
   folder resten af teksten ud.
4. **Footer på telefonen:** tryk på din e-mail og dit telefonnummer — de
   skal åbne mail-appen og telefonen.
5. **Bundmenuen** på en lille telefon: alle fem knapper kan ses, også "Mere".
