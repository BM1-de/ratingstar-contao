# RatingStar für Contao

Bindet das RatingStar-Siegel und die Google-Sterne (Rich Snippets) einer
RatingStar-Filiale (https://ratingstar.de) in eine Contao-Website ein.

**Status: in Entwicklung.**

## Funktionsumfang

Bereits umgesetzt:

- **Website-Konfiguration** auf der Root-Seite (RatingStar-Einstellungen):
  Profil-Slug, API-Key (`rs_live_…`, Format-validiert) und optionale
  Base-Origin (Default `https://ratingstar.de`, kein Host hartkodiert).
- **Inhaltselement „RatingStar-Siegel"** (`rs_seal`): voller Varianten-Satz
  (Rundsiegel, Banderole, Profil-Karte, Bewertungsleiste, Gesamtnote, Zitat,
  Karussell, Bewertungswand, Fußleiste), Position der Profil-Karte (im Inhalt
  oder schwebend in einer Ecke), Farbschema hell/dunkel für Bewertungsleiste,
  Gesamtnote, Zitat, Karussell und Bewertungswand und Slug-Override pro
  Element. Ältere `floating`-Elemente laufen als Profil-Karte weiter. Rendert das offizielle
  Embed-Markup und lädt `seal.js` einmal pro Seite (async, on demand).

Geplant (folgende Iterationen):

- Server-seitiges JSON-LD (`LocalBusiness`, key-basiert über
  `/seal/k/<key>.jsonld`, ein Block pro Seite).
- Statischer SVG-Fallback, DSGVO-/Consent- und Domain-Whitelist-Hinweise.

## Konfiguration

1. **Root-Seite** im Backend öffnen → Abschnitt **RatingStar**: Profil-Slug
   (und optional API-Key / Base-Origin) eintragen.
2. Auf einer Seite ein Inhaltselement **RatingStar-Siegel** anlegen, Variante
   wählen, optional einen abweichenden Slug setzen.

## Entwicklung

Dieses Repo enthält **nur das Bundle**. Die lokale Test-Instanz liegt
drumherum (DDEV-Projekt `ratingstar-plugin-contao`, Contao Managed
Edition 5), das Bundle ist dort unter `packages/contao-ratingstar-bundle`
ausgecheckt — Einrichtung siehe `SETUP.md` im Projektordner.
