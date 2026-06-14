# Upload-guide til Simply.com — AI-synligheds-opdatering

Denne guide viser præcis hvilke filer du skal lægge op, og — vigtigst — hvilke du
**aldrig** må overskrive (ellers mister du indhold og statistik fra den live side).

## Sådan kommer du til filerne på Simply
1. Log ind på **mit.simply.com** → vælg dit webhotel for `zpolonius.dk`.
2. Åbn **Filhåndtering** (eller brug FTP/SFTP med fx FileZilla — login-oplysninger står under "FTP" i kontrolpanelet).
3. Gå ind i webroden — typisk mappen **`public_html`** (nogle gange `www` eller en mappe opkaldt efter domænet). Det er den mappe hvor `index.html` allerede ligger.

---

## 1) NYE filer — skal lægges op (findes ikke på serveren endnu)

| Lokal fil | Læg i (på serveren) |
|-----------|---------------------|
| `detail.php` | webroden (samme sted som `index.html`) |
| `llms.txt` | webroden |
| `api/log_ai_bot.php` | mappen `api/` |
| `detail.php` | webroden |
| `projects.php` | webroden |
| `insights.php` | webroden |
| `cv.php` | webroden |
| `recommendations.php` | webroden |
| `about.php` | webroden |

## 2) ÆNDREDE filer — upload og overskriv de gamle

Webroden:
- `index.html`
- `contact.html`
- `robots.txt`
- `sitemap.php`
- `.htaccess`  ← se note nedenfor
- `admin.html`

Mappen `api/`:
- `api/track.php`
- `api/analytics.php`

## 3) SLET disse filer på serveren

De er erstattet af `.php`-versioner. Hvis de gamle `.html`-filer bliver liggende,
kan serveren komme til at vise den forkerte (gamle) version.

- **`detail.html`** → erstattet af `detail.php`
- **`projects.html`** → erstattet af `projects.php`
- **`insights.html`** → erstattet af `insights.php`
- **`cv.html`** → erstattet af `cv.php`
- **`recommendations.html`** → erstattet af `recommendations.php`
- **`about.html`** → erstattet af `about.php`

> `index.html` og `contact.html` forbliver `.html` — dem skal du IKKE slette.

---

## ⚠️ Rør ALDRIG disse på serveren (vil ødelægge live data)

Disse filer "lever" på serveren og bliver ændret af admin-panelet og af de besøgende.
Hvis du uploader dine lokale kopier oven i dem, **sletter du indhold og statistik**:

- ❌ `data/content.json` — alt dit indhold (redigeres via admin på den live side)
- ❌ `data/analytics.json` — din besøgsstatistik (oprettes automatisk)
- ❌ `api/config.php` — adgangskode og hemmeligheder (server-specifik)

Kort sagt: **upload ikke mappen `data/`, og rør ikke `api/config.php`.**

---

## Vigtige noter

**.htaccess**
Hvis Simply's filhåndtering ikke vil vise/uploade en fil der starter med punktum, så
upload den evt. som `htaccess.txt` og **omdøb den til `.htaccess`** bagefter. Tjek at
der findes en `.htaccess` i både webroden og i `api/` og `data/`.

**PHP-version**
Under webhotellets indstillinger på Simply: sørg for **PHP 7.4 eller nyere** er valgt.
`detail.php` bruger ingen eksotiske funktioner, men 7.4+ er forudsætningen.

**Cache**
Efter upload: lav en hard refresh i browseren (Ctrl+F5). HTML-filerne har allerede
`?v=1.0.7` på `main.js`, så JS opdateres automatisk.

---

## Test bagefter (5 minutter)

1. **SSR virker:** åbn `https://zpolonius.dk/projects/bring-checkout-advisory` →
   højreklik → **Vis sidekilde** (View Source). Du skal kunne se titel, brødtekst og
   en `<script type="application/ld+json">` direkte i kilden (ikke bare tomme div'er).
2. **Schema er gyldigt:** indsæt samme URL i
   [Google Rich Results Test](https://search.google.com/test/rich-results).
3. **llms.txt:** åbn `https://zpolonius.dk/llms.txt` — skal vise din profiltekst.
4. **robots.txt:** åbn `https://zpolonius.dk/robots.txt` — skal nævne GPTBot, ClaudeBot osv.
5. **AI-tracking:** log ind i admin → **Statistik**. Det nye kort "AI-trafik (30 dage)"
   skal være synligt. (Tallene starter på 0 og vokser, efterhånden som AI-bots crawler
   og folk klikker ind fra AI-svar.)
