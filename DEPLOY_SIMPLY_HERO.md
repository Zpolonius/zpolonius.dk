# Upload-guide til Simply.com — ny hero på forsiden

Denne opdatering rører **kun forsidens hero**. Resten af siden — farver, sektioner,
bento-grid, navigation, admin — er uændret.

Tre filer skal op. Intet skal slettes.

## Sådan kommer du til filerne på Simply
1. Log ind på **mit.simply.com** → vælg dit webhotel for `zpolonius.dk`.
2. Åbn **Filhåndtering** (eller FTP/SFTP med fx FileZilla — login står under "FTP" i kontrolpanelet).
3. Gå ind i webroden — typisk **`public_html`**. Det er mappen hvor `index.html` allerede ligger.

---

## 1) NY fil — skal lægges op

| Lokal fil | Læg i (på serveren) |
|-----------|---------------------|
| `assets/figure.webp` | mappen `assets/` |

Det er dit fritlagte portræt, 818×1150 WebP med gennemsigtig baggrund, 87 KB.
Uden den fil står hero'en med tekst og gradient, men ingen figur.

## 2) ÆNDREDE filer — upload og overskriv de gamle

| Lokal fil | Læg i (på serveren) |
|-----------|---------------------|
| `index.html` | webroden |
| `js/main.js` | mappen `js/` |

---

## ⚠️ Rør ALDRIG disse på serveren

Uændret fra den forrige guide — de lever på serveren og bliver skrevet af admin
og af de besøgende:

- ❌ `data/content.json` — alt dit indhold
- ❌ `data/analytics.json` — din besøgsstatistik
- ❌ `api/config.php` — adgangskode og hemmeligheder

**Denne hero kræver ingen ændring i `data/content.json`.** Den henter navn, roller
og pitch fra den content.json der allerede ligger på serveren.

## Behold `assets/cover.webp`

Det gamle brede coverfoto bruges ikke længere i hero'en, men det er stadig dit
delebillede på LinkedIn og Facebook (`og:image`). **Slet det ikke.**

---

## Efter upload

Lav en hard refresh (**Ctrl+F5**). `main.js` er bumpet til `?v=1.0.9` på forsiden,
så den nye version hentes automatisk.

### Test — 2 minutter

1. **Figuren er der:** åbn `https://zpolonius.dk` — dit portræt står til højre,
   teksten til venstre, og de rører ikke hinanden.
2. **Skrivemaskinen kører:** pillen øverst med den grønne prik skifter mellem
   "Checkout Arkitekt", "AI Entusiast" og "Digital Brobygger".
3. **Begge temaer:** tryk på sol/måne-ikonet i menuen. Hero'en skal skifte med —
   mørk bund i dark mode, lys grå bund i light mode.
4. **Telefon:** åbn siden på mobilen. Figuren ligger som et svagt motiv i højre
   kant, knappen går i fuld bredde, og metarækken nederst viser kun
   "Technical Account Manager hos Bring".

---

## Godt at vide bagefter

**Rollerne i pillen redigeres i admin** under Forside → Hero → Rolle, adskilt med `|`.
Kun roller på **24 tegn eller derunder** ruller i pillen — det holder pillens bredde
i ro. Dine tre lange sætninger ("Bygger bro mellem kompleks teknologi…",
"Hjulpet +100 webshops…", "Optimererer checkout-flows…") står stadig i feltet, men
vises ikke i hero'en. Vil du have dem frem, så skriv dem kortere.

**Hero-billedet i admin** (Forside → Hero → Cover) peger stadig på det gamle
`cover.webp`. Det ignoreres bevidst af koden, netop så den live content.json ikke
ødelægger den nye hero. Uploader du et nyt billede dér, bliver det brugt som figur —
så det skal være **fritlagt med gennemsigtig baggrund**, ellers får du en firkantet
kasse i hero'en.

**De tre fakta nederst** ("Technical Account Manager hos Bring", "Checkout · CRO ·
Levering", "Bjæverskov, Danmark") står fast i `index.html` og redigeres ikke via
admin. Skal de ændres, er det i `index.html` under `<div class="hero-meta">`.
