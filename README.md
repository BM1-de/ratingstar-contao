# RatingStar für Contao

Bindet das RatingStar-Siegel und die Google-Sterne (Rich Snippets) einer
RatingStar-Filiale (https://ratingstar.de) in eine Contao-Website ein.

**Status: in Entwicklung** — Funktionsumfang und Roadmap stehen in den
Issues dieses Repos.

## Geplanter Funktionsumfang

- Bundle-Konfiguration: RatingStar-Profil-Slug + Embed-Key
- Siegel-Widget (Rundsiegel, Banderole, Floating-Badge) als
  Contao-Inhaltselement
- Google-Sterne: serverseitiges JSON-LD über den offiziellen
  RatingStar-Endpunkt (`/seal/k/<key>.json`)

## Entwicklung

Dieses Repo enthält **nur das Bundle**. Die lokale Test-Instanz liegt
drumherum (DDEV-Projekt `ratingstar-plugin-contao`, Contao Managed
Edition 5), das Bundle ist dort unter `packages/contao-ratingstar-bundle`
ausgecheckt — Einrichtung siehe `SETUP.md` im Projektordner.
