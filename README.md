# RatingStar für Contao

RatingStar ist das Bewertungstool für Unternehmen: Kundenbewertungen sammeln, Bewertungen aus Google und anderen Portalen bündeln und alles mit Siegeln auf der eigenen Website zeigen. Dieses Bundle bringt die Siegel und Widgets von [RatingStar](https://ratingstar.de) in Ihre Contao-Website und liefert Google die Daten für die Sterne in der Suche.

**Installation:** über den Contao Manager (Suche nach „RatingStar“) oder `composer require ratingstar/contao-seal-bundle`.

## Funktionen

- **Inhaltselement „RatingStar-Siegel“** mit allen neun Varianten: Rundsiegel, Banderole, Profil-Karte, Bewertungsleiste, Gesamtnote, Zitat, Karussell, Bewertungswand und Fußleiste.
- **Einstellungen je Website** an der Root-Seite: Profil-Slug, Gruppe, API-Key, Google-Sterne und RatingStar-Adresse. Mehrere Websites in einer Contao-Installation bekommen eigene Einstellungen.
- **Google-Sterne (JSON-LD):** Der Server holt die Bewertungsdaten ab und schreibt sie als strukturierte Daten in den Seitenkopf. Der Browser Ihrer Besucher braucht dafür keine Verbindung zu RatingStar.
- **Optionen je Element:** Größe, Farben, Anzahl der Bewertungen und mehr. Jede Option steht zunächst auf „Standard (Portal)“; dann gilt, was Sie im RatingStar-Konto eingestellt haben.
- **Siegel einer Gruppe** über alle Filialen, für die ganze Website oder ein einzelnes Element.
- **Statisches Bild** ohne JavaScript für Rundsiegel, Banderole und Profil-Karte.
- **Twig-Funktion** `ratingstar_seal()` für Layouts und eigene Templates.
- Oberfläche in Deutsch und Englisch.

## Voraussetzungen

- Contao 5.3 oder neuer (entwickelt und geprüft mit Contao 5.7)
- PHP 8.2 oder neuer
- ein RatingStar-Konto

## Installation

**Contao Manager:** nach `ratingstar/contao-seal-bundle` suchen, hinzufügen und die Änderungen anwenden. Der Manager aktualisiert anschließend die Datenbank.

**Composer:**

```bash
composer require ratingstar/contao-seal-bundle
vendor/bin/contao-console contao:migrate
```

## Einrichtung

1. Im Backend unter **Seitenstruktur** die Root-Seite Ihrer Website bearbeiten und den Abschnitt **RatingStar** öffnen.
2. **Profil-Slug der Filiale** eintragen: der Teil hinter `/t/` in der Adresse Ihres RatingStar-Profils (bei `https://ratingstar.de/t/muster-gmbh` also `muster-gmbh`).
3. Für die Google-Sterne zusätzlich den **API-Key** eintragen und **Google-Sterne ausgeben (JSON-LD)** ankreuzen. Den Key finden Sie im RatingStar-Konto auf der Seite „Google-Sterne“; er beginnt mit `rs_live_`.
4. Speichern. Contao prüft den Slug gegen Ihr Profil und zeigt den gefundenen Profilnamen an. Findet es kein Profil, erscheint ein Hinweis; gespeichert wird in jedem Fall, damit Sie einen Tippfehler in Ruhe korrigieren können.
5. Die Domain der Website bei RatingStar freigeben (nächster Abschnitt).
6. Auf einer Seite ein Inhaltselement **RatingStar-Siegel** anlegen (Rubrik „Include-Elemente“) und die Variante wählen.

| Feld an der Root-Seite | Bedeutung |
|---|---|
| Profil-Slug der Filiale | Wessen Bewertungen die Siegel der Website zeigen. |
| Gruppe (Slug) | Nur für das Siegel einer ganzen Gruppe, siehe „Siegel einer Gruppe“. |
| API-Key | `rs_live_` und 40 Zeichen. Wird für die Google-Sterne gebraucht und bleibt gültig, wenn sich der Slug ändert. |
| Google-Sterne ausgeben (JSON-LD) | Schreibt die strukturierten Daten in den Kopf jeder Seite. |
| RatingStar-Adresse | Leer lassen. Nur nötig, wenn Sie eine eigene RatingStar-Instanz nutzen. |

## Domain freigeben

RatingStar liefert die Daten für Siegel und Widgets nur an Websites, die Sie freigegeben haben:

1. Im RatingStar-Konto **Siegel & Widgets → Domains & Statistik** öffnen.
2. Unter „Freigegebene Domains“ die Domain Ihrer Website eintragen.
3. Den angezeigten DNS-Eintrag (`TXT _ratingstar.<ihre-domain>`) bei Ihrem Domain-Anbieter setzen und die Domain bestätigen.

Der Schutz greift, sobald die erste Domain bestätigt ist. Ab dann erhalten nur noch bestätigte Domains die Siegel-Daten; auf allen anderen bleibt das Siegel leer (die Abfrage wird mit HTTP 403 abgelehnt). Das bloße Eintragen einer Domain schaltet noch nichts ab. Tragen Sie deshalb auch Test- und Staging-Domains ein, auf denen das Siegel erscheinen soll.

Zeigen Sie das Siegel einer Gruppe, pflegen Sie die Domains im **Gruppen-Backend** an derselben Stelle. Die Freigaben der Filialen gelten nicht für das Gruppen-Siegel und umgekehrt.

Die Google-Sterne sind davon unabhängig: Sie laufen über den API-Key und funktionieren auch ohne freigegebene Domain.

## Das Inhaltselement

### Varianten

| Variante | Zeigt | Tarif |
|---|---|---|
| Rundsiegel | rundes Siegel mit Note | alle |
| Banderole | Siegel mit Banderole | alle |
| Profil-Karte | Karte mit Note und Anzahl, im Inhalt oder schwebend in einer Bildschirmecke | ab 4-Star |
| Bewertungsleiste | schmale Leiste mit Sternen und Note | ab 4-Star |
| Gesamtnote | große Note mit Sternen | ab 4-Star |
| Zitat | eine einzelne Bewertung | ab 4-Star |
| Karussell | wechselnde Bewertungen | ab 4-Star |
| Bewertungswand | mehrere Bewertungen in Spalten | ab 4-Star |
| Fußleiste | Leiste am unteren Bildschirmrand oder im Seitenverlauf | ab 4-Star |

In einem kleineren Tarif erscheint anstelle einer Variante „ab 4-Star“ automatisch die Banderole. Das entscheidet RatingStar bei jedem Aufruf; nach einem Tarifwechsel müssen Sie in Contao nichts ändern.

### Optionen

Nach der Wahl der Variante blendet Contao die passenden Optionen ein, zum Beispiel Größe, Kartenfarbe, Anzahl der Bewertungen, Spalten oder Farbschema. Jede Option steht zunächst auf **Standard (Portal)**: Dann gilt die Einstellung aus Ihrem RatingStar-Konto, und eine spätere Änderung dort wirkt sofort auf der Website. Nur was Sie am Element ausdrücklich wählen, überschreibt das Portal für dieses eine Element. So können Sie dieselbe Variante mehrfach mit unterschiedlichem Aussehen einsetzen.

Die **Position** der Profil-Karte legt fest, ob sie im Inhalt steht oder schwebend in einer der vier Bildschirmecken.

### Andere Filiale oder Gruppe

Ohne weitere Angabe zeigt das Element die Filiale oder Gruppe der Root-Seite. Mit **Andere Filiale (Profil-Slug)** oder **Gruppe (Slug)** zeigt ein einzelnes Element etwas anderes, etwa das Siegel eines Partners. Auch diese Angaben prüft Contao beim Speichern, ohne das Speichern zu verhindern.

### Als statisches Bild ausgeben

Für Rundsiegel, Banderole und Profil-Karte gibt es das Siegel auch als Bild ohne JavaScript, verlinkt auf Ihr Profil. Das eignet sich für Druck- oder Newsletter-Fassungen einer Seite. Das Bild zeigt den aktuellen Stand Ihrer Bewertungen; von den Optionen wirkt hier nur die Größe oder Breite. Für die übrigen Varianten gibt es kein statisches Motiv, deshalb fehlt dort das Ankreuzfeld.

### Zusätzliche data-Attribute

Für Fachleute: Im RatingStar-Konto erzeugt der Code-Generator unter „Siegel & Widgets“ für jede Einstellung ein Attribut, zum Beispiel `data-size="240" data-pc-color="weiss"`. Diese Attribute können Sie in das Feld **Zusätzliche data-Attribute** kopieren. Sie überschreiben gleichnamige Optionen des Elements. Angenommen werden nur `data-`Attribute; Slug, Gruppe, Variante und Position stellen Sie über die eigenen Felder ein.

## Google-Sterne (JSON-LD)

Ist **Google-Sterne ausgeben (JSON-LD)** angekreuzt und ein API-Key eingetragen, schreibt das Bundle in den Kopf jeder Seite der Website genau einen Block mit strukturierten Daten vom Typ `LocalBusiness`. Google kann daraus die Sterne neben Ihrem Suchergebnis bilden. Ob und wann Google Sterne anzeigt, entscheidet allein Google.

- Note und Anzahl enthält der Block ab dem Tarif 4-Star und sobald es Bewertungen gibt, die bei RatingStar selbst abgegeben wurden. Aus anderen Portalen übernommene Bewertungen zählen hier nicht mit. Darunter enthält der Block nur den Namen und den Link zum Profil.
- Die Daten holt Ihr Server bei RatingStar ab und hält sie 6 Stunden vor. Ist RatingStar einmal nicht erreichbar oder der Key ungültig, fehlt der Block; die Seite selbst bleibt unverändert. Der nächste Versuch folgt nach 15 Minuten.
- Die Siegel der Website verzichten dann auf ihren eigenen Datenblock, damit die Angaben nicht doppelt auf der Seite stehen.
- Rotieren Sie den Key im RatingStar-Konto, wird der alte ungültig. Tragen Sie den neuen Key an der Root-Seite ein.

## Siegel einer Gruppe

Mehrere Filialen lassen sich bei RatingStar zu einer Gruppe zusammenfassen. Deren Siegel zählt die Bewertungen aller aktiven Filialen.

- **Für die ganze Website:** an der Root-Seite **Gruppe (Slug)** eintragen, den Teil hinter `/g/` in der Adresse der Gruppe. Als API-Key gehört dann der Schlüssel der Gruppe hinein; Sie finden ihn im Gruppen-Backend auf der Seite „Google-Sterne“.
- **Für ein einzelnes Element:** das Feld **Gruppe (Slug)** am Inhaltselement füllen.

Ist eine Gruppe gesetzt, hat sie Vorrang vor dem Profil-Slug. In der Variante Gesamtnote steht unter der Gesamtnote die Note jeder Filiale; die Option **Note je Filiale zeigen** blendet das aus.

Für die Google-Sterne gilt: je Seite ein Block. Auf der Website einer Gruppe trägt er die Daten der Gruppe, auf der Website einer einzelnen Filiale die der Filiale.

## Für Theme-Entwickler: Twig-Funktion

In Layouts und anderen Twig-Templates bindet `ratingstar_seal()` ein Siegel ein, mit denselben Möglichkeiten wie das Inhaltselement:

```twig
{# Filiale oder Gruppe der Root-Seite #}
{{ ratingstar_seal('bar') }}

{# Profil-Karte schwebend unten rechts, mit Optionen #}
{{ ratingstar_seal('profile-card', {position: 'bottom-right', options: {pcColor: 'weiss', pcWidth: 180}}) }}

{# andere Filiale, zusätzliche Attribute aus dem Code-Generator #}
{{ ratingstar_seal('carousel', {slug: 'muster-gmbh', attributes: 'data-car-count="4" data-car-width="s"'}) }}

{# Gruppe als statisches Bild #}
{{ ratingstar_seal('seal-circle', {group: 'muster-gruppe', static: true}) }}
```

| Angabe | Bedeutung |
|---|---|
| erster Parameter | Variante: `seal-circle`, `seal-circle-banner`, `profile-card`, `bar`, `hero`, `quote`, `carousel`, `wall`, `footer-bar` |
| `slug`, `group` | andere Filiale oder Gruppe als an der Root-Seite |
| `position` | nur Profil-Karte: `inline`, `bottom-right`, `bottom-left`, `top-right`, `top-left` |
| `static` | `true` gibt ein Bild ohne JavaScript aus (Rundsiegel, Banderole, Profil-Karte) |
| `options` | Optionen mit ihrem Namen aus dem Code-Generator, zum Beispiel `{carCount: 4, theme: 'dark'}` |
| `attributes` | zusätzliche `data-`Attribute als Text oder als Liste `{'data-size': 240}` |

Das Skript `seal.js` wird je Seite nur einmal geladen, gleichgültig wie viele Siegel sie zeigt. Das Template des Inhaltselements heißt `content_element/ratingstar_seal` und lässt sich wie jedes Contao-Template überschreiben.

## Caching und Cache leeren

| Was | Wo | Dauer |
|---|---|---|
| Daten für die Google-Sterne | auf Ihrem Server | 6 Stunden, nach einem Fehler 15 Minuten |
| Daten der Siegel und Widgets | im Browser der Besucher | etwa 1 Minute |

Den Zwischenspeicher für die Google-Sterne leeren Sie so:

- Sie speichern die RatingStar-Einstellungen der Root-Seite mit einer Änderung, oder
- im Backend unter **Systemwartung → Daten bereinigen** die Aufgabe **RatingStar-Daten leeren** ausführen, oder
- auf der Konsole: `vendor/bin/contao-console cache:pool:clear ratingstar_seal.cache`

Auch das Erneuern des Anwendungs-Caches (`cache:clear` auf der Konsole oder über den Contao Manager) entfernt die Daten. Nutzen Sie den Seitencache von Contao, enthalten bereits zwischengespeicherte Seiten den alten Stand, bis auch dieser Cache abläuft oder geleert wird.

Nimmt der Zwischenspeicher keine Einträge an (Ordner `var/cache` ohne Schreibrecht, Redis nicht erreichbar oder schreibgeschützt), gibt das Bundle keine Google-Sterne aus und fragt RatingStar nicht ab. Im Log (`var/logs`) steht dann ein Fehler, der mit „RatingStar: the cache pool“ beginnt.

## Content Security Policy

Haben Sie an der Root-Seite die Content Security Policy von Contao eingeschaltet, meldet das Bundle die RatingStar-Adresse selbst als erlaubte Quelle für Skripte, Stile, Datenabrufe und Bilder an, und das Skript erhält die Nonce der Seite. Sie müssen dafür nichts eintragen.

Die Widgets legen einzelne Maße und Farben als Inline-Stil fest. Eine Richtlinie, die Inline-Stile verbietet (etwa nur `default-src 'self'`), blockiert diese Angaben: Die Widgets erscheinen und zeigen ihre Daten, Abstände, Breiten und Farben können aber vom Portal abweichen. Soll alles wie im Portal aussehen, erlauben Sie Inline-Stile in der Richtlinie der Root-Seite: `style-src 'self' 'unsafe-inline'`.

Setzen Sie die Richtlinie an anderer Stelle, etwa im Webserver, tragen Sie die RatingStar-Adresse dort für `script-src`, `style-src`, `connect-src` und `img-src` ein.

## Datenschutz

Siegel und Widgets laden beim Seitenaufruf ein Skript, eine Stildatei, die Bewertungsdaten und ein Zählpixel von RatingStar (`ratingstar.de`). Der Browser Ihrer Besucher baut dabei eine Verbindung zu RatingStar auf und überträgt wie bei jedem Abruf seine IP-Adresse und die Adresse der aufrufenden Seite. Das Zählpixel zählt, wie oft ein Siegel angezeigt und angeklickt wurde.

- Nennen Sie RatingStar in Ihrer Datenschutzerklärung als Empfänger.
- Berücksichtigen Sie den Dienst in Ihrem Consent-Tool, wenn Sie externe Inhalte erst nach Einwilligung laden.
- Das statische Bild lädt kein Skript und kein Zählpixel, wird aber ebenfalls von RatingStar abgerufen.
- **Die Google-Sterne brauchen keine Verbindung des Browsers zu RatingStar.** Die Daten holt Ihr Server ab; dabei werden keine Daten Ihrer Besucher übertragen. Wer auf Siegel verzichtet und nur die Google-Sterne nutzt, bindet also nichts Externes in die Seite ein.

Datenschutzerklärung von RatingStar: <https://ratingstar.de/datenschutz>

## Eigene RatingStar-Instanz

Alle Adressen bildet das Bundle aus einer einzigen Einstellung. Für eine Test- oder eigene Instanz ändern Sie sie für alle Websites in `config/config.yaml`:

```yaml
ratingstar_seal:
    base_origin: 'https://bewertungen.example.com'
```

oder für eine einzelne Website im Feld **RatingStar-Adresse** der Root-Seite. Einzutragen ist die Adresse ohne Pfad.

## Wenn etwas nicht erscheint

- **Kein Siegel auf der Seite:** Ist an der Root-Seite ein Profil-Slug oder eine Gruppe eingetragen? In der Vorschau des Elements im Backend steht, welche Filiale oder Gruppe es zeigt.
- **Der Platz bleibt leer:** Prüfen Sie die Freigabe der Domain im RatingStar-Konto. Blockiert ein Consent-Tool oder ein Werbeblocker die Verbindung zu RatingStar, bleibt der Platz ebenfalls leer.
- **Statt des Widgets erscheint die Banderole:** Die gewählte Variante gehört zu einem höheren Tarif.
- **Keine Sterne bei Google:** Prüfen Sie API-Key und Ankreuzfeld, leeren Sie den Zwischenspeicher und sehen Sie im Seitenquelltext nach einem Block `application/ld+json` mit `LocalBusiness`. Google zeigt Sterne erst nach einem erneuten Besuch der Seite und nach eigenem Ermessen.

## Entwicklung

Der Code des Bundles liegt in `src/`, die Contao-Dateien (DCA, Sprachdateien, Templates) in `contao/`. Die Optionen des Inhaltselements entstehen aus `src/Seal/SealOptions.php`, dem Abbild der von `seal.js` unterstützten Einstellungen.

## Lizenz

GNU General Public License, Version 2 oder später (GPL-2.0-or-later), siehe `LICENSE`.
