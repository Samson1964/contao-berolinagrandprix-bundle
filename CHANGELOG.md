# Berolina-GrandPrix Changelog

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
