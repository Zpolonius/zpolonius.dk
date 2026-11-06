# Roadmap — zpolonius.dk

Designforbedringer fundet ved gennemgang af den kørende site 08-09-2026 samt hero-arbejdet 19-09-2026.

**Sådan læses listen.** Hvert punkt beskriver *problemet* og *hvorfor det betyder noget*, ikke en gættet løsning. Hvor der er et reelt valg at træffe, står det under **Beslutning**. Punkter uden beslutning er determinerede — der er kun én rigtig udgang, og en kodningsagent kan gå direkte i gang.

**Status-felter:** `Beslutning: ingen` = klar til implementering. `Beslutning: påkrævet` = skal afklares med Zacharias først, ellers gætter agenten.

**Målte værdier** stammer fra DOM-inspektion af den kørende side. De kan have ændret sig; verificér før du retter.

---

## NU — determineret, kan implementeres uden afklaring

### N1 · Knapkontrast under WCAG AA
**Problem.** Den primære knapfarve er `--blue: #3b82f6`. Med hvid tekst på 13px giver det et kontrastforhold på **3,68:1**. WCAG 2.1 AA kræver 4,5:1 for tekst under 18,66px bold / 24px regular.
**Omfang.** Alle primære CTA'er: `Book et review`, `Gratis checkout tjek`, `Send besked`, `Luk vindue`, `Acceptér alle` (cookiebanner).
**Hvorfor.** Det er sitets eneste rene tilgængelighedsfejl i tekstkontrast, og den rammer præcis de elementer der skal konvertere. Én token-ændring løser alle fem.
**Beslutning: ingen.** Farven skal mørkne indtil den består. `#2563EB` giver ca. 5,2:1, `#1D4ED8` ca. 7:1. Vælg den mørkeste der stadig ser rigtig ud i begge temaer.
**Acceptkriterie.** Alle knapper med hvid tekst måler ≥ 4,5:1 mod deres egen baggrund i både lyst og mørkt tema. Verificeres med et kontrastværktøj, ikke på øjemål.
**Teknisk.** `:root { --blue }`. Bemærk at `--border-blue: rgba(65,143,255,.35)` er en *anden* blå — tjek om den skal følge med.

### N2 · Footerens sociale ikoner ligger bag den faste bundnavigation
**Problem.** På mobil har `body` `padding-bottom: 0`. Den faste bundnav måler 66,8px høj og ligger på `bottom: 840` ved `innerHeight: 856`. Footerens sociale ikoner sidder på `top: 749–769` ved fuld scroll og bliver delvist dækket. `footer` har kun 16px bundpadding.
**Hvorfor.** Det læses som en fejl, ikke som design, og det er det sidste brugeren ser på siden.
**Beslutning: ingen.**
**Acceptkriterie.** Ved maksimal scroll på 375×812 og 390×844 er alle footer-elementer fuldt synlige over bundnavigationen, inkl. på enheder med hjemmeindikator (`env(safe-area-inset-bottom)`).
**Teknisk.** Bundpadding på `body` eller på footeren svarende til navhøjde + safe area. Undgå en hårdkodet px-værdi hvis navhøjden kan ændre sig.

### N3 · Overskriftsniveauer springer et trin over
**Problem.** Rækkefølgen på forsiden er `H1 → H3 × 6 → H2 → H2 …`. De seks profilkort bruger `H3` uden en `H2` over sig.
**Hvorfor.** Skærmlæsere navigerer på overskriftsstruktur; et spring signalerer et niveau der ikke findes. Det påvirker også hvordan søgemaskiner læser sidens disposition.
**Beslutning: påkrævet — hvad skal `H2`'en hedde?** Sektionen har i dag ingen synlig overskrift. Enten tilføjes en synlig `H2` (ændrer layoutet), eller der tilføjes en visuelt skjult `H2` (ændrer intet visuelt, men er en halv løsning). Begge er legitime; valget er redaktionelt.
**Acceptkriterie.** Overskriftsrækkefølgen på forsiden stiger uden spring. Verificeres med en overskriftsoversigt, ikke ved gennemlæsning af koden.

### N4 · Ingen skip-link til hovedindhold
**Problem.** Der findes ingen `skip-link` før navigationen. Tastaturbrugere skal igennem hele topnavigationen på hver side.
**Hvorfor.** Standardkrav i WCAG 2.4.1. Sitet har allerede `:focus`-styles og respekterer `prefers-reduced-motion`, så tilgængelighedsfundamentet er der — dette er hullet.
**Beslutning: ingen.**
**Acceptkriterie.** Første Tab-tryk på en frisk sideindlæsning afslører et synligt link der springer fokus til hovedindholdet. Skal virke på både forside og undersider.

### N5 · Implementér den nye hero
**Problem.** Dagens hero lægger hvid tekst direkte på et fotografi uden scrim — kun `text-shadow: 0 1px 8px rgba(0,0,0,.6)` på afsnittet. Over billedets lyse partier er brødteksten ulæselig på desktop. Underrubrikken er 14px mod en `H1` på 52px.
**Hvorfor.** Det er sidens første element og bærer positioneringen. Den nye hero fjerner problemet ved at adskille tekst og billede i stedet for at dæmpe det.
**Beslutning: ingen** — kompositionen er godkendt. Koden skal skrives ud fra den godkendte mockup.
**Acceptkriterie.** Hero'en fungerer ved 375, 560, 820, 1000, 1440 og 1920px i begge temaer. Teksten overlapper aldrig figuren. Ingen `text-shadow` er nødvendig. Billedet har `width`, `height`, `alt=""`, `aria-hidden="true"` og `fetchpriority="high"`. Ingen CLS ved indlæsning.
**Teknisk.** Hero'en bruger container-queries (`container-type: inline-size` på hero'ens wrapper), ikke media-queries — verificér browserunderstøttelse mod sitets supportmatrix, og afklar om der skal en fallback til.
**Afhænger af.** N1 (knapfarven indgår i hero'en).

### N6 · "Book et review" brækker over to linjer på mobil
**Problem.** Knappens tekst wrapper ved 375px bredde.
**Hvorfor.** Kosmetisk, men det er sidens primære konverteringselement.
**Beslutning: påkrævet — kortere tekst eller `nowrap`?** Afhænger af udfaldet af N7. Hvis CTA-teksten alligevel omskrives, løses dette der.
**Acceptkriterie.** Primær CTA står på én linje fra 320px og op.

---

## NÆST — kræver en beslutning før kodning

### X1 · Fire konkurrerende CTA'er over folden
**Problem.** Ved første sidevisning er synlige samtidig: cookiebanner, flydende knap `Book en ærlig snak` (`button.floating-cta`, `position: fixed`), nav-CTA `Gratis checkout tjek`, hero-CTA `Book et review` og sekundær `Se Business Cases`. Fem opfordringer, hvoraf fire konkurrerer.
**Hvorfor.** Det er den samme friktion sitet selv argumenterer imod i artiklen om "1–5 hverdage": tøven koster konvertering. Flere ligeværdige valg producerer tøven.
**Beslutning: påkrævet — hvilke CTA'er må være synlige samtidig?** Mulige akser: skal den flydende knap først vises efter scroll forbi hero? Skal nav-CTA'en forsvinde mens hero-CTA'en er i viewport? Skal cookiebanneret blokere de øvrige indtil det er besvaret? Der er ikke ét rigtigt svar — det afhænger af hvad Zacharias vil have folk til at gøre først.
**Acceptkriterie.** Defineres når beslutningen er truffet. Bør formuleres som en regel, ikke som en px-værdi: "ved første load er præcis N primære handlinger synlige".

### X2 · Samme handling har fire forskellige navne
**Problem.** `Book et review`, `Gratis checkout tjek`, `Book en ærlig snak` og `Send besked` fører til beslægtede eller identiske handlinger.
**Hvorfor.** Gentagelse bygger genkendelse; variation skaber tvivl om hvorvidt det er det samme man klikker på. Det er også et sporingsproblem — fire navne for én konvertering er svære at måle på.
**Beslutning: påkrævet — hvilket navn vinder, og er handlingerne faktisk identiske?** Først skal det afklares om `Gratis checkout tjek` og `Book et review` er samme tilbud. Hvis ja: ét navn. Hvis nej: navnene skal gøre forskellen tydelig.
**Acceptkriterie.** Hver distinkt handling har præcis ét navn, brugt konsistent på tværs af nav, hero, flydende knap og footer.
**Afhænger af.** Bør afklares før X1, da antallet af CTA'er afhænger af hvor mange distinkte tilbud der faktisk findes.

### X3 · Cookiebanneret dækker 40% af mobilskærmen
**Problem.** `div.cookie-banner.show` er `position: fixed` og dækker på 375×812 hele den nederste del af skærmen inklusive bundnavigationen. Teksten fylder tre linjer.
**Hvorfor.** Førstegangsbesøgende på mobil kan ikke se sitets navigation før de har svaret. Kombineret med X1 er det den værste del af førstegangsoplevelsen.
**Beslutning: påkrævet — hvor meget tekst er juridisk nødvendig i første visning?** Teksten nævner indlejret indhold fra Spotify. Om detaljen kan flyttes til et "læs mere"-lag er et samtykkejuridisk spørgsmål, ikke et designspørgsmål. Afklares før der skæres.
**Acceptkriterie.** Banneret dækker højst ~25% af viewporthøjden på 375×812, og bundnavigationen er ikke skjult bag det.

### X4 · Kortenes accentfarver følger intet system
**Problem.** De seks profilkort skifter mellem blå og grøn overskriftsfarve: `Mellem kode og krone` blå, `Friske øjne` grøn, `Din checkout` blå, `Mennesket før systemerne` grøn. Rækkefølgen svarer ikke til nogen synlig kategori.
**Hvorfor.** Farve er et af de stærkeste signaler i en grænseflade. Når den skifter uden betydning, lærer brugeren at ignorere den — og så virker den heller ikke dér hvor den skal bære mening.
**Beslutning: påkrævet — betyder farverne noget?** Enten defineres en regel (fx blå = kommercielt/teknisk, grøn = menneskeligt/værdibaseret) og kortene farves efter den, eller også reduceres til én accentfarve. Zacharias skal sige hvilken.
**Acceptkriterie.** Farvevalget på hvert kort kan begrundes med én sætning der gælder for alle seks.

### X5 · De seks profilkort har identisk visuel vægt
**Problem.** Alle seks kort har samme størrelse, ramme, radius og typografi. `Din checkout fortæller en historie / Checkout & CRO` — sitets erklærede speciale — står visuelt lige med `Familiefar fra Bjæverskov`.
**Hvorfor.** Uden vægtforskel læses sektionen som en liste man scroller forbi. Hvis alt er lige vigtigt, er intet det.
**Beslutning: påkrævet — hvad er rangordenen?** Kræver at Zacharias siger hvilke et, to eller tre kort der skal løftes, og hvad de øvrige må tabe. Layoutet (dobbelt bredde, accentkant, større type) følger af den beslutning, ikke omvendt.
**Acceptkriterie.** En førstegangsbesøgende kan på to sekunder pege på hvilket kort der er det vigtigste.

---

## SENERE — konsistens og polish

### S1 · Label-typografien har tre forskellige størrelser til samme rolle
**Problem.** Målte størrelser for versal-labels med samme funktion: 9px (`KONTAKT` i bundnav, `CHECKOUT`, `INDSIGT · 4 MIN.`), 10px (`DIGITAL BROBYGGE`, `SIDER`, `PLATFORM & VÆRKTØJER`, `SOCIAL`), 12px (`PROFIL`, `TILGANG`, `SPECIALE`, `FUNDAMENT`, `MENNESKET`, årstal i CV).
**Hvorfor.** Tre størrelser til én rolle betyder at der ikke findes en token — værdierne er sat ad hoc og vil fortsætte med at divergere.
**Beslutning: påkrævet — hvilken størrelse bliver standarden, og er 9px overhovedet acceptabelt?** 9px versaler er under praktisk læsegrænse for mange brugere.
**Acceptkriterie.** Alle versal-labels bruger samme token. Ingen labeltekst er under den valgte minimumsstørrelse.

### S2 · Footerens kontaktoplysninger er 11px
**Problem.** `zacharias@polonius.dk` og `3068 7041` er sat i 11px, ligesom footerens sociale links.
**Hvorfor.** Det er data folk skal kunne læse, kopiere og trykke på — ikke dekoration. Det er også den eneste direkte kontaktvej i footeren.
**Beslutning: påkrævet — skal kontaktdata behandles som tekst eller som label?** Hvis tekst, bør de op i brødtekststørrelse og adskilles visuelt fra labelrækken de ligger i.
**Acceptkriterie.** E-mail og telefonnummer er læsbare uden zoom på mobil og har et berøringsmål på mindst 44×44px.

### S3 · Hero-billedaktivet skal dokumenteres
**Problem.** Det fritlagte portræt er behandlet i flere trin (modelvalg til baggrundsfjernelse, beskæring, alfa-erodering, tonning, WebP-eksport). Ingen af trinene er dokumenteret i repoet.
**Hvorfor.** Når billedet skal udskiftes — nyt portræt, ny beskæring, lyst tema — skal trinene kunne gentages. Ellers ser version to anderledes ud end version et.
**Beslutning: ingen.**
**Acceptkriterie.** Der ligger en kort note i repoet ved siden af billedet med: kildefil, anvendt fritlægningsmodel, beskæring, tonningsværdier og eksportindstillinger.

---

## Verificeret i orden — rør ikke

Målt ved gennemgangen. Nævnt så en kodningsagent ikke "retter" noget der allerede er rigtigt.

- Alle 10 billeder på forsiden har `alt`-tekst
- `<html lang="da">` er sat
- `:focus`-regler findes i stylesheetet
- `@media (prefers-reduced-motion)` respekteres
- Semantisk `<main>` findes
- Meta description er sat og velformuleret
- Én skrifttype (Inter) i hele sitet — ingen fallback-inkonsistens
- Bundnavigationen på mobil er en velvalgt løsning på et dybt site

---

## Åbne spørgsmål der blokerer flere punkter

1. **Er `Gratis checkout tjek` og `Book et review` samme tilbud?** Blokerer X2, som blokerer X1 og N6.
2. **Hvad er rangordenen mellem de seks profilkort?** Blokerer X5 og X4.
3. **Hvor meget cookietekst er juridisk nødvendig i første visning?** Blokerer X3.
4. **Understøtter sitets supportmatrix container-queries?** Påvirker implementeringen af N5.
