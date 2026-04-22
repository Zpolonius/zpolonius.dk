<?php
/**
 * VIGTIGT: Slet denne fil STRAKS efter brug!
 * Den må ikke ligge på serveren permanent.
 *
 * Sådan bruger du den:
 * 1. Upload til public_html/zpolonius/generate-hash.php
 * 2. Åbn i browser: zpolonius.dk/zpolonius/generate-hash.php
 * 3. Kopier hashen ind i api/config.php
 * 4. SLET denne fil med det samme
 */

// Skriv dit ønskede password her:
$password = 'SkiftMigTilDitPassword';

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
?>
<!DOCTYPE html>
<html lang="da">
<head>
  <meta charset="UTF-8">
  <title>Hash Generator — Slet efter brug</title>
  <style>
    body { font-family: monospace; background: #0f0f0f; color: #e8e8e8; padding: 40px; max-width: 700px; }
    h2 { color: #e05555; margin-bottom: 8px; }
    .warning { background: rgba(224,85,85,0.1); border: 1px solid #e05555; padding: 16px; margin-bottom: 24px; font-size: 13px; line-height: 1.6; }
    .label { font-size: 11px; color: rgba(255,255,255,0.4); letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 8px; }
    .hash { background: #161616; border: 1px solid rgba(255,255,255,0.1); padding: 16px; word-break: break-all; font-size: 13px; color: #41A447; margin-bottom: 24px; cursor: pointer; }
    .steps { font-size: 13px; color: rgba(255,255,255,0.6); line-height: 2; }
    .steps b { color: #418FFF; }
  </style>
</head>
<body>
  <h2>⚠ Slet denne fil straks efter brug!</h2>
  <div class="warning">
    Denne fil viser dit password-hash. Slet den fra serveren med det samme efter du har kopieret hashen — ellers er den tilgængelig for alle.
  </div>

  <div class="label">Dit password</div>
  <div class="hash"><?= htmlspecialchars($password) ?></div>

  <div class="label">Kopier denne hash ind i api/config.php</div>
  <div class="hash" onclick="navigator.clipboard.writeText(this.textContent).then(()=>this.style.color='#418FFF')" title="Klik for at kopiere">
    <?= htmlspecialchars($hash) ?>
  </div>

  <div class="steps">
    <b>Næste trin:</b><br>
    1. Klik på hashen ovenfor for at kopiere den<br>
    2. Åbn <b>api/config.php</b> i Simply's File Manager<br>
    3. Erstat værdien af <b>ADMIN_PASSWORD_HASH</b> med hashen<br>
    4. Gem filen<br>
    5. <b style="color:#e05555">GÅ TILBAGE TIL FILE MANAGER OG SLET DENNE FIL!</b>
  </div>
</body>
</html>
