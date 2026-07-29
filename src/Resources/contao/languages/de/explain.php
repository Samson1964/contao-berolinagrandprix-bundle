<?php

/**
 * Hilfetext zum CSV-Feld der Turniertabelle
 *
 * Hinweis: Die Zuordnung erfolgt über die Spaltenposition, nicht über den
 * Spaltennamen. Die erste Zeile wird immer als Kopfzeile übersprungen.
 */
$GLOBALS['TL_LANG']['XPL']['grandprix_csv'] = array
(
	array('colspan', 'Geben Sie hier die Daten der Turniertabelle im CSV-Format ein. Zeilen müssen durch einen Zeilenumbruch, Spalten durch ein Semikolon getrennt sein.<br><br>Die erste Zeile wird <b>immer</b> als Kopfzeile interpretiert und übersprungen. Die Zuordnung erfolgt über die <b>Spaltenposition</b>:'),
	array('1. Spalte', 'Platz - Zahl mit oder ohne Punkt, z.B. "1" oder "1."'),
	array('2. Spalte', 'Name - im Format "Nachname,Vorname". Der Name muss exakt mit dem Namen in der Teilnehmerliste übereinstimmen.'),
	array('colspan', 'Weitere Spalten sind erlaubt, werden aber nicht ausgewertet. Leere Zeilen und Zeilen ohne gültige Platzziffer werden übersprungen.'),
);
