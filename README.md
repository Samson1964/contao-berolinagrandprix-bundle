# Berolina-GrandPrix

Contao-Erweiterung zur Berechnung und Anzeige einer Grand-Prix-Wertung über
mehrere Turniere hinweg (SV Berolina Mitte).

**Frank Hoppe**

## Voraussetzungen

* PHP 8.1 oder neuer
* Contao 4.13 oder Contao 5
* [MultiColumnWizard](https://github.com/menatwork/contao-multicolumnwizard-bundle) 3.6+

## Installation

```
composer require schachbulle/contao-berolinagrandprix-bundle
```

Anschließend die Datenbank über den Contao-Manager oder
`vendor/bin/contao-console contao:migrate` aktualisieren.

## Verwendung

1. Im Backend unter **Inhalte → Berolina Grand-Prix** eine Wertung anlegen:
   Wertungspunkte je Platz, DWZ-Grenze zwischen Kategorie A und B, Anzahl der
   zu wertenden besten Turniere und die Wertungsreihenfolge.
2. Die Teilnehmer entweder von Hand erfassen oder per CSV importieren
   (je Zeile `Name,Vorname[Trennzeichen]DWZ`).
3. Zu jeder Wertung die einzelnen Turniere mit ihrer Ergebnistabelle im
   CSV-Format anlegen. Erste Zeile = Kopfzeile, 1. Spalte = Platz,
   2. Spalte = `Nachname,Vorname`. Spalten werden durch `;` getrennt.
4. Im Frontend das Inhaltselement **Berolina Grand-Prix-Wertung** einfügen und
   die Wertung sowie die Anzahl der anzuzeigenden Turniere auswählen.

### Wertungslogik

* Teilnehmer oberhalb der DWZ-Grenze gehören zur Kategorie A, alle anderen zur
  Kategorie B. Teilnehmer der Kategorie B werden zusätzlich in der
  Gesamtwertung A geführt.
* Die Wertungspunkte richten sich nach dem Rang **innerhalb der Kategorie**,
  nicht nach dem Gesamtplatz des Turniers. Gäste und Nicht-Mitglieder belegen
  keinen Wertungsrang.
* Bei gleichem Gesamtplatz werden die Wertungspunkte je nach Einstellung
  entweder geteilt oder alle Beteiligten erhalten die höchste Wertung.
* Gewertet werden nur die besten *x* Turniere; die übrigen erscheinen
  durchgestrichen.

## Tests

```
vendor/bin/phpunit
```

Die Rechenlogik liegt in `src/Calculator/GrandPrixCalculator.php` und ist ohne
Contao-Bootstrap testbar.
