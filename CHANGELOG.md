# Berolina-GrandPrix Changelog

## Version 2.1.0 (2026-07-29)

Das Bundle läuft jetzt **gleichzeitig unter Contao 4.13 und Contao 5** (PHP 8.1+).
Verifiziert in einer Contao-4.13-Installation (Symfony 5.4) und einer
Contao-5.7.7-Installation (Symfony 7.4), jeweils mit identischer Frontend-Ausgabe
und ohne PHP-Warnungen oder Deprecations.

### composer.json

* Change: `contao/core-bundle` von `^5.3` auf `^4.13 || ^5.0`
* Change: `symfony/dependency-injection` auf `^5.4 || ^6.4 || ^7.0`, dazu
  `symfony/config` und `symfony/http-kernel` ergänzt
* Change: `menatwork/contao-multicolumnwizard-bundle` von `*` auf `^3.6 || ^4.0`
* Delete: `doctrine/doctrine-cache-bundle` aus `require-dev` entfernt (obsolet)
* Add: `phpunit/phpunit: ^9.5` in `require-dev`, `autoload-dev` für die Tests

### Behobene Fehler

* Fix: **Fatal Error** `Unsupported operand types: string + int` beim Auswerten
  einer Turnier-CSV mit Leerzeile am Ende (ab PHP 8). Leerzeilen, Zeilen ohne
  Platzziffer und Zeilen ohne Namen werden jetzt übersprungen.
* Fix: Nicht gespielte Turniere wurden als leere Streichwertung `<s></s>`
  ausgegeben; durchgestrichen werden jetzt nur tatsächlich gespielte Turniere.
* Fix: Ohne gesetzte Wertungsreihenfolge sortierte `array_multisort()` die
  Teilnehmerliste nach dem kompletten Datensatz und die Plazierungsspalte blieb
  komplett leer. Ohne Wertungsreihenfolge bleibt die Reihenfolge jetzt erhalten.
* Fix: Die Plazierung wurde über zusammengesetzte, auf 2 bzw. 4 Zeichen
  abgeschnittene Strings verglichen (Kollisionen ab 100 Punkten). Verglichen
  werden jetzt die Wertungsfelder selbst.
* Fix: `explode()` auf einen leeren `excluded`-Wert und fehlende Spalten aus dem
  MultiColumnWizard erzeugten Deprecations bzw. „Undefined array key".
* Fix: Leere oder nicht numerische Wertungspunkte (z.B. `10,,6`) führten ab
  PHP 8 zu einem TypeError.
* Fix: Die Option **„Nullwertungen anzeigen"** (`viewnull`) war im DCA definiert,
  wurde aber nie ausgewertet. Ist sie deaktiviert, werden Teilnehmer ohne
  Wertungspunkte jetzt ausgeblendet.
* Fix: `System::setCookie('BE_PAGE_OFFSET', …)` im Import entfernt – das Cookie
  existiert weder in Contao 4.13 noch in Contao 5.
* Fix: Der Import legte die Version erst nach dem Schreiben an, sodass die
  Vorgängerversion nicht wiederherstellbar war (`initialize()` fehlte).
* Fix: Import brach mit „Undefined array key 1" ab, wenn eine CSV-Zeile keine
  DWZ-Spalte enthielt.
* Fix: `show.gif` → `show.svg` als Icon der Turnier-Operation.
* Fix: Palette `berolina-grandprix` enthielt die Felder `guest` und `space`, die
  es in `tl_content` weder in Contao 4.13 noch in Contao 5 gibt; dafür ist jetzt
  `customTpl` enthalten (eigenes Template wählbar).
* Fix: Fehlendes Sprachlabel `tl_content.editalias` (führte zu `sprintf(null)`).
* Fix: Doppelte Definition von `tl_berolina_grandprix_tournaments.edit` und
  diverse Tippfehler in den Sprachdateien.
* Fix: Der Hilfetext zur CSV-Tabelle beschrieb eine Spaltenerkennung über
  Spaltennamen, die es im Code nie gab. Er beschreibt jetzt die tatsächliche
  Zuordnung über die Spaltenposition.

### Umbau und Bereinigung

* Add: Neue Klasse `Calculator\GrandPrixCalculator` – die komplette Rechenlogik
  ohne Contao-Abhängigkeit und damit ohne Framework-Bootstrap testbar.
* Add: 25 Unit-Tests in `tests/` samt `phpunit.xml.dist` und Bootstrap, der auch
  ohne eigenes `vendor/`-Verzeichnis funktioniert.
* Change: `ContentElements\GrandPrix` lädt nur noch die Daten und delegiert die
  Berechnung; das Element zeigt im Backend jetzt einen Platzhalter statt der
  kompletten Tabelle.
* Change: Die Suche nach Teilnehmern je CSV-Zeile lief linear über die gesamte
  Teilnehmerliste; jetzt über ein Namensregister. Die ausgeschlossenen Turniere
  werden einmalig statt je CSV-Zeile aufgelöst.
* Delete: Toter Code entfernt – `NameDrehen()`, `NameKonvertieren()`,
  `getTemplates()`, das nirgends ausgewertete Array `$platzanzahl`, leere
  `buttons_callback`/`__selector__`/`subpalettes`-Blöcke und die Sprachdatei
  `tl_module.php` (Übersetzungen eines anderen Bundles).
* Change: `services.yml` → `services.yaml` mit `autowire`/`autoconfigure`.
* Change: `declare(strict_types=1)` in allen Klassendateien, Imports statt
  vollqualifizierter Namen im Rumpf, `DataContainer::MODE_*`/`SORT_*`-Konstanten
  statt Zahlen.
* Change: Template `ce_grandprix.html5` nutzt jetzt `block_searchable` (der alte
  Wrapper griff auf das in Contao 5 entfernte `$this->margin` zu), rendert beide
  Kategorien über eine Schleife, maskiert Teilnehmernamen, ist gegen fehlende
  Werte abgesichert und zeigt einen Hinweis, wenn eine Kategorie leer ist.
* Change: `fgetcsv()` wird mit leerem `$escape`-Parameter aufgerufen (der
  Standardwert `\` gilt ab PHP 8.4 als veraltet).

### Hinweise

* Die Datenbankstruktur ändert sich nicht – ein Update erfordert keine Migration.
* Die Wertungspunkte richten sich weiterhin nach dem Rang **innerhalb der
  Kategorie**, nicht nach dem Gesamtplatz. Gäste und Nicht-Mitglieder belegen
  keinen Wertungsrang.

## Version 2.0.0 (2026-06-28)

Komplette Anpassung an **Contao 5.7** und **PHP 8.4**. In Contao 5 entfernte
Konstanten, globale Klassen-Aliase, globale Funktionen und Backend-URLs wurden
durch die entsprechenden Klassen bzw. Services ersetzt. Verifiziert in einer
Contao-5.7.7-Installation (Symfony 7.4).

### composer.json

* Change: `php` von `^5.6 || ^7 || ^8` auf `^8.1` angehoben (deckt PHP 8.4 ab)
* Change: `contao/core-bundle` von `^4` auf `^5.3`
* Add: `symfony/dependency-injection: ^6.4 || ^7.0`
* Delete: Abhängigkeit `codefog/contao-haste` entfernt – die Toggle-Funktion wird
  jetzt über den Contao-5-Core abgebildet (siehe unten)
* Change: Beschreibung „für Contao 4" → „für Contao 5"

### Behobene Fatal Errors (in Contao 5 entfernte APIs)

* Fix: `_instanceof`-Block für `Symfony\Component\DependencyInjection\ContainerAwareInterface`
  aus `services.yml` entfernt – dieses Interface existiert in Symfony 7 nicht mehr
  und ließ die Container-Kompilierung scheitern
* Fix: `dataContainer => 'Table'` → `\Contao\DC_Table::class` in `tl_berolina_grandprix`
  und `tl_berolina_grandprix_tournaments` („Attempted to load class 'Table' from the
  global namespace")
* Fix: Globale Klassen ohne Namespace (`\Database`, `\Input`, `\System`, `\File`,
  `\Environment`, `\Message`, `\Versions`, `\Image`, `\Backend`, `\ContentElement`,
  `\DataContainer`, `\Date`, `\Config`) auf `\Contao\...` umgestellt
  (`ContentElements\GrandPrix`, `Classes\Import`, alle DCA-Dateien)
* Fix: Globale Funktionen `ampersand()` und `specialchars()` (entfernt) ersetzt durch
  `\Contao\StringUtil::ampersand()` / `::specialchars()` in `Classes\Import` und `tl_content`
* Fix: Konstante `REQUEST_TOKEN` (entfernt) ersetzt durch
  `contao.csrf.token_manager`->`getDefaultTokenValue()` in `Classes\Import` (Import-Formular)
  und `tl_content::editListe`
* Fix: Backend-URL `contao/main.php?do=...` (existiert nicht mehr) in `tl_content::editListe`
  durch die Router-Route `contao_backend` ersetzt
* Fix: Konstante `TL_ROOT` (entfernt) – `if (!defined('TL_ROOT')) die(...)`-Wächter aus
  `dca/tl_content.php` und `languages/de/explain.php` entfernt (hätten die Dateien beim
  Laden sofort abgebrochen)
* Fix: Konstruktoren mit `$this->import('BackendUser', 'User')` aus `tl_berolina_grandprix`,
  `tl_berolina_grandprix_tournaments` und `tl_content_berolina_grandprixlist` entfernt –
  der globale Alias `BackendUser` existiert in Contao 5 nicht mehr und `$this->User`
  wurde nirgends benutzt
* Fix: DWZ-Import (`Classes\Import`) nutzt jetzt direkt `\Contao\FileUpload` statt der
  nicht mehr funktionierenden `$this->User->uploader`-Logik

### Toggle-Funktion (Haste → Core)

* Change: `tl_berolina_grandprix.published` und `tl_berolina_grandprix_tournaments.published`
  – `haste_ajax_operation` durch die Core-Toggle-Operation (`act=toggle&field=published`)
  ersetzt und `'toggle' => true` am Feld gesetzt

### Weitere Fixes (PHP 8 / Korrektheit)

* Fix: Operations-Icons der Backend-Listen von `.gif` auf `.svg` umgestellt
  (`edit`, `editheader`/`header`, `copy`, `delete`, `show`) – Contao 5 liefert nur noch
  SVG-Icons aus, die alten GIF-Dateien fehlten (defekte Bilder in der Backend-Liste)
* Fix: `unserialize()` durch `\Contao\StringUtil::deserialize(..., true)` ersetzt in
  `ContentElements\GrandPrix` – `count()` auf dem Rückgabewert `false` (leeres Blob-Feld)
  warf in PHP 8 einen `TypeError`
* Fix: Ausgabe-Arrays `$tabelleA`/`$tabelleB` in `ContentElements\GrandPrix::compile()`
  vorbelegt – sonst „Undefined variable" unter PHP 8, wenn keine Meisterschaft gefunden wird
* Fix: Zugriffe auf das Wertungspunkte-Array in `ContentElements\GrandPrix::Wertungspunkte()`
  mit `?? 0` abgesichert (keine „Undefined array key"-Warnungen, wenn mehr Teilnehmer als
  Wertungsstufen vorhanden sind)

### Hinweise (kein Codeeingriff)

* Note: Zugriffe über `$this->Database` bzw. `$this->import('Database')` funktionieren in
  Contao 5 weiterhin (Backwards-Compatibility), lösen aber ab Contao 5.2 eine Deprecation
  aus und sollten für Contao 6 auf `\Contao\Database::getInstance()` umgestellt werden.

## Version 1.1.0 (2025-01-26)

* Add: tl_berolina_grandprix.evaluation_order -> Feinwertungen vereinheitlicht
* Delete: tl_berolina_grandprix.evaluation_order_A, evaluation_order_B, evaluation_order_C, evaluation_order_D, better_points, higher_tourns entfernt
* Fix: Wertungsoptionen zu Ende programmiert

## Version 1.0.3 (2024-06-07)

* Fix: Warning: Undefined array key "punkte" in ContentElements/GrandPrix.php (line 309) 

## Version 1.0.2 (2024-06-06)

* Fix: Warning: Undefined array key 1 in ContentElements/GrandPrix.php (line 509) -> Code aus Chesstable-Bundle übernommen
* Fix: Warning: Undefined array key 1 in ContentElements/GrandPrix.php (line 121) 
* Fix: Warning: Undefined array key 8 in ContentElements/GrandPrix.php (line 439) 
* Fix: Warning: Undefined array key 4 in ContentElements/GrandPrix.php (line 152) 
* Fix: Warning: Undefined array key "punkte" in ContentElements/GrandPrix.php (line 282) 

## Version 1.0.1 (2023-02-28)

* Fix: Syntaxfehler in composer.json
* Add: Kompatibilität mit PHP 8 in composer.json

## Version 1.0.0 (2023-02-27)

* Add: Abhängigkeit codefog/contao-haste
* Add: Abhängigkeit menatwork/contao-multicolumnwizard-bundle
* Change: tl_berolina_grandprix -> Toggle-Funktion ausgetauscht gegen Haste-Toggler
* Change: tl_berolina_grandprix_tournaments -> Toggle-Funktion ausgetauscht gegen Haste-Toggler
* Fix: Template ce_grandprix -> Fehler bei leerem Array
* Fix: Edit-Link im Inhaltselement für die ausgewählte Meisterschaft

## Version 0.0.5 (2020-05-18)

* Fix: print_r Ausgabe entfernt

## Version 0.0.4 (2020-04-21)

* New: Import von DWZ-Listen

## Version 0.0.3 (2020-04-19)

* Korrektur composer.json

## Version 0.0.2 (2020-04-19)

* Korrektur composer.json

## Version 0.0.1 (2020-04-19)

* Übernahme der Version aus Contao 3
