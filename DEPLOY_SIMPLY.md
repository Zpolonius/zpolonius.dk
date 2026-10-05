# Deploy-plan til Simply.com

Én samlet plan for at få alt fra PR #9–#12 i luften: tilgængelighed, roadmap-
beslutningerne, origami-tranen og oprydningen. Den erstatter de tidligere
guides (`DEPLOY_SIMPLY_HERO.md`, `_A11Y.md` og `_ROADMAP.md`).

**Tid:** ca. 30 minutter inkl. backup og test.
**Hvornår:** når alle PR'er er merget til `main`. Upload fra en frisk kopi af `main`.

---

## Trin 0 — Hent den nyeste kode

Upload altid fra `main`, ikke fra en feature-branch.

- **GitHub:** gå til repoet → grøn **Code**-knap → **Download ZIP**. Pak den ud.
- **Eller lokalt:** `git checkout main && git pull`.

---

## Trin 1 — Tag backup (5 min) ⚠️ spring ikke over

Så kan du altid rulle tilbage, hvis noget ser forkert ud.

1. Log ind på **mit.simply.com** → vælg webhotellet for `zpolonius.dk`.
2. Åbn **Filhåndtering** (eller FTP/SFTP med fx FileZilla — login står under
   "FTP" i kontrolpanelet). Gå ind i webroden, typisk **`public_html`** — mappen
   hvor `index.html` ligger.
3. **Download** disse til en mappe på din computer, fx `backup-2026-10-05`:
   - hele webroden **undtagen** `assets/` (billederne ændres ikke)
   - `data/content.json` og `data/analytics.json` (dit indhold og din statistik)

---

## Trin 2 — Tjek PHP-versionen (1 min)

Under webhotellets indstillinger på Simply: vælg **PHP 8.1 eller nyere**
(minimum 7.4). Statistik-koden er nu skrevet, så den virker på alle nyere
versioner uden advarsler.

---

## Trin 3 — Upload 17 filer (10 min)

Upload **alle** i samme omgang, og overskriv de gamle. Siderne henter nye
versioner af CSS og JavaScript (`?v=1.5.0`), så alle dele skal være på plads
samtidig.

### Webroden

| Fil | Bemærkning |
|---|---|
| `.htaccess` | Starter med punktum — se note nedenfor |
| `index.html` | |
| `contact.html` | |
| `vacation-reply.html` | |
| `404.html` | |
| `cv-print.html` | |
| `admin.html` | |
| `about.php` | |
| `cv.php` | |
| `detail.php` | |
| `insights.php` | |
| `projects.php` | |
| `recommendations.php` | |

### Mapper

| Fil | Læg i |
|---|---|
| `css/style.css` | `css/` |
| `js/main.js` | `js/` |
| `js/origami.js` | `js/` (ny, hvis tranen ikke er oppe endnu) |
| `api/track.php` | `api/` |

**Note om `.htaccess`:** filen er skjult i mange FTP-programmer — slå "vis
skjulte filer" til. Vil Simply's filhåndtering ikke tage imod den, så upload den
som `htaccess.txt` og omdøb den til `.htaccess` bagefter.

**Tjek også:** at `assets/figure.webp` (hero-portrættet) findes på serveren.
Den kom med hero-opdateringen i september. Mangler den, så upload den til `assets/`.

---

## ⛔ Rør ALDRIG disse på serveren

De "lever" på serveren og ændres af admin og besøgende. Uploader du dine lokale
kopier, **sletter du indhold eller statistik**:

- ❌ `data/content.json` — alt dit indhold (heller ikke selvom den er ændret i repoet)
- ❌ `data/analytics.json` — din besøgsstatistik
- ❌ `api/config.php` — adgangskode og hemmeligheder

Upload heller ikke `.md`-filerne, `generate-hash.php` eller mappen `.claude/`.
De bruges kun under udvikling.

---

## Trin 4 — Sæt profilkortene i admin (5 min)

Farver og fremhævning ligger i dit indhold, ikke i koden.

1. Log ind på `zpolonius.dk/admin` → **Forside**.
2. Tryk **Rediger** ud for hvert kort, sæt værdierne og tryk **Gem**:

| Kort | Accent-farve | Fremhæv boksen |
|---|---|---|
| Mellem kode og krone | Blå | ✅ |
| Jeg siger det ingen andre tør | Blå | – |
| Din checkout fortæller en historie | Blå | ✅ |
| Mennesket før løsningen | Grøn | – |
| AI er ikke fremtiden | Blå | ✅ |
| Familiefar fra Bjæverskov | Grøn | – |

Listen viser "★ Fremhævet" ved de tre.

---

## Trin 5 — Test (10 min)

Åbn siderne i et **privat vindue** (så du ser det som en ny besøgende), og lav
en hard refresh (**Ctrl+F5**) i dit normale vindue.

**Vigtigst — kontaktformularen**
1. Gå til `/contact` **på din telefon**. Formularen "Send en besked" skal stå
   under kontaktoplysningerne — ikke ude til højre.
2. Udfyld og send en testbesked til dig selv. Du skal se "tak"-visningen og
   modtage **præcis én** mail.

**Forsiden**
3. Kun én blå "Tjek min checkout →" er synlig ved første visning, med
   "Gratis og uforpligtende" under.
4. Cookiebanneret fylder ca. en femtedel af telefonskærmen. "Læs mere" folder
   teksten ud.
5. "Kort fortalt" står over profilkortene. Tre kort er bredere med blå kant
   (efter trin 4).
6. Scroll ned: tranen følger med og folder sig ud til en seddel. Klik på
   tranen → siden flyver ned. "Tilbage til hvor du var" bringer dig tilbage.

**Tastatur og mobil**
7. Tryk **Tab** én gang: "Spring til hovedindhold" dukker op øverst til venstre.
8. På telefonen: siden kan ikke skubbes sidelæns, og footerens ikoner, e-mail
   og telefon står frit over bundmenuen. E-mail og telefon kan trykkes på.

**Tablet / lille laptop** (eller gør browservinduet ca. 900px bredt)
9. Topmenuen står på én linje. "Om mig", "CV", "Anbefalinger" og
   "Ferie-autosvar" ligger under ☰-knappen.

**Admin**
10. Log ind → **Statistik**. Siden indlæses normalt. Står der underlige
    sidenavne med `<` eller `>`, er det gamle forsøg på at misbruge statistikken —
    de vises nu som harmløs tekst.

---

## Hvis noget går galt — rul tilbage

1. Upload filerne fra din backup-mappe (trin 1) oven i de nye.
2. **Upload ikke** `data/`-filerne fra backuppen, medmindre de faktisk er blevet
   ødelagt — ellers mister du det, der er kommet til siden.
3. Hard refresh (**Ctrl+F5**).

Fortæl mig, hvad der så forkert ud, så finder vi fejlen før næste forsøg.

---

## Godt at vide bagefter

- **Sider caches ikke længere i en måned.** Næste gang du opdaterer, ser
  besøgende ændringen med det samme. CSS og JavaScript caches stadig i et år —
  derfor skal versionsnummeret (`?v=…`) altid op, når de ændres.
- **Statistik fra lokal test** kan aldrig komme med i en upload:
  `data/analytics.json` er udelukket i git.
