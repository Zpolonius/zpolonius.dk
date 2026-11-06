-- =====================================================================
-- zpolonius.dk – Checkout-scanner
-- Migration 001: opretter scannerens tabeller
--
-- Rører IKKE eksisterende tabeller eller content.json.
-- Alle tabeller har præfikset chk_, så de ikke kolliderer med andet
-- i samme database. Kører man filen to gange, fejler den med
-- "table already exists" i stedet for stille at springe over –
-- så opdager man det, hvis skemaet allerede findes.
--
-- Kræver MySQL 8.0.16+ for at CHECK-regler håndhæves
-- (ældre versioner accepterer syntaksen, men ignorerer reglerne).
--
-- VIGTIGT: Reglerne virker kun i strict mode. Forbind altid via api/db.php,
-- som slår strict mode til. Kør også denne fil i en session med strict:
--   SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE';
-- =====================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 1. Fund-typer: den faste liste agenterne må vælge fra.
--    Agenten finder selv på teksten, men ALDRIG typen eller linket.
--    Så kan en snedig webshop-side (prompt injection) ikke få
--    rapporten til at linke til en fremmed adresse.
-- ---------------------------------------------------------------------
CREATE TABLE chk_finding_types (
  code          VARCHAR(64)  NOT NULL,                 -- fx 'levering_vag_dato'
  area          ENUM('levering','betaling','retur_tillid','mobil_hastighed','tekst') NOT NULL,
  label         VARCHAR(160) NOT NULL,                 -- intern beskrivelse
  article_slug  VARCHAR(160) NULL,                     -- "Læs hvorfor →" – NULL = intet link
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. Scans: én række pr. analyse. Fungerer også som jobkø
--    (status queued → running → done/failed).
-- ---------------------------------------------------------------------
CREATE TABLE chk_scans (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id       CHAR(32)        NOT NULL,            -- tilfældig nøgle i URL'er; id vises aldrig udadtil
  domain          VARCHAR(253)    NOT NULL,            -- normaliseret, fx 'dinwebshop.dk'
  url_submitted   VARCHAR(2048)   NOT NULL,
  status          ENUM('queued','running','done','failed') NOT NULL DEFAULT 'queued',
  error_code      VARCHAR(64)     NULL,                -- fx 'blocked', 'timeout' – aldrig rå fejltekst
  attempts        TINYINT UNSIGNED NOT NULL DEFAULT 0,
  claimed_by      VARCHAR(64)     NULL,                -- hvilken worker tog jobbet
  findings_total  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  human_reviewed  TINYINT(1)      NOT NULL DEFAULT 0,  -- har Zacharias læst rapporten igennem?
  ip_hash         BINARY(32)      NULL,                -- SHA-256 af IP + hemmeligt salt; aldrig rå IP
  created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  started_at      DATETIME        NULL,
  finished_at     DATETIME        NULL,
  expires_at      DATETIME        NULL,                -- cache: genbrug resultatet til denne dato
  PRIMARY KEY (id),
  UNIQUE KEY uq_scans_public_id (public_id),
  KEY ix_scans_queue (status, created_at),             -- workeren henter ældste job i kø
  KEY ix_scans_cache (domain, status, expires_at),     -- "er denne shop scannet for nylig?"
  CONSTRAINT ck_scans_attempts CHECK (attempts <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. Fund: de 12 fund pr. scan. is_free = 1 på de 3 gratis.
-- ---------------------------------------------------------------------
CREATE TABLE chk_findings (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  scan_id         BIGINT UNSIGNED NOT NULL,
  type_code       VARCHAR(64)     NOT NULL,
  severity        TINYINT UNSIGNED NOT NULL,           -- 1 = lav, 2 = middel, 3 = høj
  is_positive     TINYINT(1)      NOT NULL DEFAULT 0,  -- "Behold det" (grønne fund)
  title           VARCHAR(160)    NOT NULL,
  body            TEXT            NOT NULL,
  evidence_text   TEXT            NULL,                -- udsnit fra siden som bevis
  screenshot_path VARCHAR(255)    NULL,                -- relativ sti, aldrig en ekstern URL
  is_free         TINYINT(1)      NOT NULL DEFAULT 0,
  sort_order      SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_findings_order (scan_id, sort_order),
  KEY ix_findings_type (type_code),
  CONSTRAINT fk_findings_scan FOREIGN KEY (scan_id) REFERENCES chk_scans (id) ON DELETE CASCADE,
  CONSTRAINT fk_findings_type FOREIGN KEY (type_code) REFERENCES chk_finding_types (code),
  CONSTRAINT ck_findings_severity CHECK (severity BETWEEN 1 AND 3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. Køb: én række pr. Stripe-betaling.
--    Ingen kortdata her – Stripe har dem.
-- ---------------------------------------------------------------------
CREATE TABLE chk_purchases (
  id                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  scan_id               BIGINT UNSIGNED NOT NULL,
  stripe_session_id     VARCHAR(255)    NOT NULL,
  stripe_payment_intent VARCHAR(255)    NULL,
  status                ENUM('pending','paid','refunded','failed') NOT NULL DEFAULT 'pending',
  amount_ore            INT UNSIGNED    NOT NULL,      -- beløb i øre: undgår afrundingsfejl
  currency              CHAR(3)         NOT NULL DEFAULT 'DKK',
  email                 VARCHAR(254)    NOT NULL,      -- hvor rapport-linket sendes hen
  access_token_hash     BINARY(32)      NOT NULL,      -- SHA-256 af token i linket; selve tokenet gemmes ikke
  withdrawal_waived_at  DATETIME        NOT NULL,      -- bevis på samtykke til at fortrydelsesret bortfalder
  created_at            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  paid_at               DATETIME        NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_purchases_session (stripe_session_id),
  UNIQUE KEY uq_purchases_token (access_token_hash),
  KEY ix_purchases_scan (scan_id),
  -- RESTRICT: en scan med et køb kan ikke slettes ved et uheld (bogføring)
  CONSTRAINT fk_purchases_scan FOREIGN KEY (scan_id) REFERENCES chk_scans (id) ON DELETE RESTRICT,
  CONSTRAINT ck_purchases_amount CHECK (amount_ore > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. Stripe-webhooks: Stripe sender samme hændelse flere gange.
--    Unik nøgle på event-id = hver hændelse behandles kun én gang.
-- ---------------------------------------------------------------------
CREATE TABLE chk_webhook_events (
  stripe_event_id  VARCHAR(255) NOT NULL,
  event_type       VARCHAR(64)  NOT NULL,
  received_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (stripe_event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. Rate limits: antal scans pr. (hashet) IP pr. tidsvindue.
-- ---------------------------------------------------------------------
CREATE TABLE chk_rate_limits (
  ip_hash       BINARY(32) NOT NULL,
  window_start  DATETIME   NOT NULL,                   -- fx starten af timen/døgnet
  scan_count    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (ip_hash, window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Startdata: fund-typer knyttet til de artikler der findes i dag.
-- article_slug = artiklens id i content.json (bruges til "Læs hvorfor →").
-- ---------------------------------------------------------------------
INSERT INTO chk_finding_types (code, area, label, article_slug) VALUES
  ('levering_vag_dato',     'levering',        'Leveringstid som interval i stedet for dato', 'derfor-draeber-1-5-hverdage-din-konvertering-og-hvordan-eta-skaber-vaekst'),
  ('levering_pris_sent',    'levering',        'Fragtpris vises først i kassen',             'undgaa-forladte-kurve-elimin-r-usynlig-friktion-i-din-e-commerce'),
  ('levering_pakkeshop_ok', 'levering',        'Valg af udleveringssted i checkout',          'den-usynlige-barriere-i-din-checkout'),
  ('retur_info_skjult',     'retur_tillid',    'Returinfo mangler i købsflowet',              'fra-udgift-til-vaekst-goer-returhaandtering-til-din-stoerste-konkurrencefordel-i-2026'),
  ('betaling_metoder',      'betaling',        'Betalingsmetoder synlige før kassen',         NULL),
  ('mobil_langsom',         'mobil_hastighed', 'Langsom indlæsning på mobil',                 NULL);
