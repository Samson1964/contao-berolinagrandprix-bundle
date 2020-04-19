<?php 

namespace Schachbulle\ContaoBerolinaGrandPrixBundle\ContentElements;

if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package   chesstable
 * Version    1.0.0
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2013
 */

class GrandPrix extends \ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_grandprix';

	/**
	 * Generate the module
	 */
	protected function compile()
	{

		// Parameter zuweisen
		$gp_liste = $this->berolina_grandprix_list; // Nummer der gewünschten Meisterschaft
		$max_turniernummer = $this->berolina_grandprix_tourcount; // Höchste zu berücksichtigende Turniernummer

		// Infos zum gewünschten Grand Prix laden
		$objGrandPrix = \Database::getInstance()->prepare('SELECT * FROM tl_berolina_grandprix WHERE published = ? AND id = ?')
		                                        ->execute(1, $gp_liste);		

		// Meisterschaftsteilnehmer einlesen
		$teilnehmerliste = array();
		if($objGrandPrix->numRows == 1)
		{
			// Meisterschaft ist online
			$teilnehmerliste = unserialize($objGrandPrix->players);
			// Teilnehmerliste um Leerfelder für einzelne Turniere, Punkte, Turnierzahl, Kategorie A/B erweitern
			for($x = 0; $x < count($teilnehmerliste); $x++)
			{
				$teilnehmerliste[$x]['kategorie'] = ($teilnehmerliste[$x]['playerdwz'] > $objGrandPrix->maxdwz) ? 'A' : 'B'; // Kategorie zuweisen
				$teilnehmerliste[$x]['turniere'] = 0;
				$teilnehmerliste[$x]['gesamtpunkteA'] = 0;
				$teilnehmerliste[$x]['gesamtpunkteB'] = 0;
				$teilnehmerliste[$x]['turnierpunkteA'] = array();
				$teilnehmerliste[$x]['turnierpunkteB'] = array();
				$teilnehmerliste[$x]['sortierungA'] = '';
				$teilnehmerliste[$x]['sortierungB'] = '';
				for($y = 1; $y <= $max_turniernummer; $y++)
				{
					$teilnehmerliste[$x]['turnierpunkteA'][$y] = false;
					$teilnehmerliste[$x]['turnierpunkteB'][$y] = false;
				}
			}

			// Wertungspunkte festlegen und Arrays verschieben von 0 ... x nach 1 ... x (Index 0 = false)
			$wertungA = explode(',', $objGrandPrix->ratingA);
			//array_unshift($wertungA, false);
			$wertungB = explode(',', $objGrandPrix->ratingB);
			//array_unshift($wertungB, false);

			// Turniere laden
			$objTurniere = \Database::getInstance()->prepare('SELECT * FROM tl_berolina_grandprix_tournaments WHERE published = ? AND pid = ? ORDER BY date ASC')
			                                       ->limit($max_turniernummer)
			                                       ->execute(1, $gp_liste);

			if($objTurniere->numRows > 0)
			{
				$platzanzahl = array(); // Nimmt die Anzahl der gleichen Platzziffern in allen Turnieren auf
				$platzreihenfolge = array(); // Nimmt die Spielernummern in Reihenfolge Plazierung für jedes Turnier und A/B auf

				$turnier = 0; // Zähler für Turniernummer
				// Turniere vorhanden, jetzt der Reihe nach in $teilnehmerliste eintragen
				while($objTurniere->next())
				{
					$turnier++;
					// CSV einlesen und übertragen, Kopfzeile wird ignoriert, 1. Spalte = Platz, 2. Spalte = Name
					$arrCSV = explode("\n", $objTurniere->csv);
					for($row = 0; $row < count($arrCSV); $row++)
					{
						$spalte = explode(";", $arrCSV[$row]); // Spalten trennen
						if($row > 0)
						{
							// Datenspalte auswerten
							$spielerplatz = $spalte[0] + 0; // Platz
							$spielername = trim($spalte[1]); // Spielername

							// Plazierung eintragen, wird später durch die Wertungspunkte ersetzt
							for($x = 0; $x < count($teilnehmerliste); $x++)
							{
								if($teilnehmerliste[$x]['playername'] == $spielername)
								{
									// Hier zuerst prüfen, ob das Turnier bei diesem Spieler ausgewertet werden darf
									if($this->TurnierErlaubt($turnier, $teilnehmerliste[$x]['excluded']))
									{

										// ***************************************************************
										// Verwendete Arrays:
										// platzreihenfolge = Sichert die Rangfolge je Kategorie und Turnier
										//                    und dazu die Spielernummer und den Gesamtplatz.
										//                    Der Array-Index entspricht der Kategorie-Plazierung
										// platzanzahl = Sichert die Anzahl der gleichen Plätze je Kategorie und
										//               Turnier
										// ***************************************************************
										$teilnehmerliste[$x]['turniere']++; // Turnieranzahl um 1 erhöhen
										switch($teilnehmerliste[$x]['kategorie'])
										{
											case 'A':
												$teilnehmerliste[$x]['turnierpunkteA'][$turnier] = $spielerplatz;
												$platzreihenfolge[$turnier]['A']['Platz'][] = $spielerplatz; // Platz zuweisen
												$platzreihenfolge[$turnier]['A']['Spieler'][] = $x; // Spielernummer zuweisen
												// Array Platzanzahl schreiben - Zwei Dimensionen: Turniernummer, Platznummer mit Zuweisung Anzahl
												$platzanzahl[$turnier]['A'][$spielerplatz]++;
												break;
											case 'B':
												$teilnehmerliste[$x]['turnierpunkteA'][$turnier] = $spielerplatz;
												$teilnehmerliste[$x]['turnierpunkteB'][$turnier] = $spielerplatz;
												$platzreihenfolge[$turnier]['A']['Platz'][] = $spielerplatz; // Platz zuweisen
												$platzreihenfolge[$turnier]['A']['Spieler'][] = $x; // Spielernummer zuweisen
												$platzreihenfolge[$turnier]['B']['Platz'][] = $spielerplatz; // Platz zuweisen
												$platzreihenfolge[$turnier]['B']['Spieler'][] = $x; // Spielernummer zuweisen
												// Array Platzanzahl schreiben - Zwei Dimensionen: Turniernummer, Platznummer mit Zuweisung Anzahl
												$platzanzahl[$turnier]['A'][$spielerplatz]++;
												$platzanzahl[$turnier]['B'][$spielerplatz]++;
												break;
										}
										break;
									}
								}
							}

						}
					}
				}

				// Platzziffern in Wertungspunkte umwandeln
				// Beginnen bei Turnier 1 bis zum max. möglichen Turnier
				for($t = 1; $t <= $max_turniernummer; $t++)
				{
					// Wertungspunkte für A-Kategorie ermitteln
					for($p = 0; $p < count($platzreihenfolge[$t]['A']['Platz']); $p++)
					{
						$platzgesamt = $platzreihenfolge[$t]['A']['Platz'][$p];
						$spielernummer = $platzreihenfolge[$t]['A']['Spieler'][$p];
						// Wertungspunkte berechnen
						$wertungspunkte = $this->Wertungspunkte($t, 'A', $p, $wertungA, $platzreihenfolge, $objGrandPrix);
						// Wertungspunkte in Teilnehmerliste eintragen, Plazierung damit ersetzen
						$teilnehmerliste[$spielernummer]['turnierpunkteA'][$t] = $wertungspunkte;
					}
					// Wertungspunkte für B-Kategorie ermitteln
					for($p = 0; $p < count($platzreihenfolge[$t]['B']['Platz']); $p++)
					{
						$platzgesamt = $platzreihenfolge[$t]['B']['Platz'][$p];
						$spielernummer = $platzreihenfolge[$t]['B']['Spieler'][$p];
						// Wertungspunkte berechnen
						$wertungspunkte = $this->Wertungspunkte($t, 'B', $p, $wertungB, $platzreihenfolge, $objGrandPrix);
						// Wertungspunkte in Teilnehmerliste eintragen, Plazierung damit ersetzen
						$teilnehmerliste[$spielernummer]['turnierpunkteB'][$t] = $wertungspunkte;
					}
				}
			}

			// Gesamtpunkte ermitteln
			for($snr = 0; $snr < count($teilnehmerliste); $snr++)
			{
				// x größte Werte finden, der Wert x steht in $objGrandPrix->max
				$arr = $teilnehmerliste[$snr]['turnierpunkteA']; // Mit Kopie des Ergebnis-Arrays arbeiten
				arsort($arr); // Absteigend sortieren, Schlüssel bleiben erhalten
				$arr = array_slice($arr, 0, $objGrandPrix->max, true); // die ersten x Werte des sortierten Arrays auslesen
				$teilnehmerliste[$snr]['gesamtpunkteA'] = array_sum($arr);

				// Feinwertung aus den besten x Turnieren generieren
				$temp = '';
				foreach ($arr as $element) 
				{
					$temp .= sprintf('%02d_', $element + 0);
				}
				$teilnehmerliste[$snr]['feinwertungA1'] = $temp;

				// Streichwerte kennzeichnen, wenn Turnierzahl größer als Maximum
				if($teilnehmerliste[$snr]['turniere'] > $objGrandPrix->max)
				{
					foreach($teilnehmerliste[$snr]['turnierpunkteA'] as $key => $value)
					{
						if(!isset($arr[$key])) $teilnehmerliste[$snr]['turnierpunkteA'][$key] = '<s>'.$teilnehmerliste[$snr]['turnierpunkteA'][$key].'</s>';
					}
				}

				// x größte Werte finden, der Wert x steht in $objGrandPrix->max
				$arr = $teilnehmerliste[$snr]['turnierpunkteB']; // Mit Kopie des Ergebnis-Arrays arbeiten
				arsort($arr); // Absteigend sortieren, Schlüssel bleiben erhalten
				$arr = array_slice($arr, 0, $objGrandPrix->max, true); // die ersten x Werte des sortierten Arrays auslesen
				$teilnehmerliste[$snr]['gesamtpunkteB'] = array_sum($arr);

				// Feinwertung aus den besten x Turnieren generieren
				$temp = '';
				foreach ($arr as $element) 
				{
					$temp .= sprintf('%02d_', $element + 0);
				}
				$teilnehmerliste[$snr]['feinwertungB1'] = $temp;

				// Streichwerte kennzeichnen, wenn Turnierzahl größer als Maximum
				if($teilnehmerliste[$snr]['turniere'] > $objGrandPrix->max)
				{
					foreach($teilnehmerliste[$snr]['turnierpunkteB'] as $key => $value)
					{
						if(!isset($arr[$key])) $teilnehmerliste[$snr]['turnierpunkteB'][$key] = '<s>'.$teilnehmerliste[$snr]['turnierpunkteB'][$key].'</s>';
					}
				}
				
			}

			// Wertungen der Reihe nach im Sortierfeld eintragen
			for($x = 0; $x < 4; $x++)
			{
				switch($x)
				{
					case 0: $wertungsoption = $objGrandPrix->evaluation_order_A; break;
					case 1: $wertungsoption = $objGrandPrix->evaluation_order_B; break;
					case 2: $wertungsoption = $objGrandPrix->evaluation_order_C; break;
					case 3: $wertungsoption = $objGrandPrix->evaluation_order_D; break;
				}
				switch($wertungsoption)
				{
					case 0: // Nichts machen, Option nicht gewählt
						break;
					case 1: // Höhere Grand-Prix-Punkte insgesamt
						for($snr = 0; $snr < count($teilnehmerliste); $snr++)
						{
							$teilnehmerliste[$snr]['sortierungA'] .= sprintf('%03d_', $teilnehmerliste[$snr]['gesamtpunkteA']);
							$teilnehmerliste[$snr]['sortierungB'] .= sprintf('%03d_', $teilnehmerliste[$snr]['gesamtpunkteB']);
						}
						break;
					case 2: // Höhere Grand-Prix-Punkte im besten Turnier
						break;
					case 3: // Höhere Anzahl der gespielten Turniere
						break;
					case 4: // Niedrigere Anzahl der gespielten Turniere
						break;
				}
			}

			print_r($teilnehmerliste);

			// Grand-Prix-Tabelle sortieren
			$tabelleA = $this->sortArrayByFields($teilnehmerliste, array('gesamtpunkteA' => SORT_DESC, 'turniere' => SORT_ASC, 'feinwertungA1' => SORT_DESC));

			// Teilnehmer mit 0 Turnieren entfernen
			$temp = array();
			for($x = 0; $x < count($tabelleA); $x++)
			{
				if($tabelleA[$x]['turniere'] > 0)
				{
					$temp[] = $tabelleA[$x];
				}
			}
			$tabelleA = $temp;

			// Plazierung eintragen
			$alt = '';
			for($x = 0; $x < count($tabelleA); $x++)
			{
				$platz = $x + 1;
				$neu = $tabelleA[$x]['punkte'].$tabelleA[$x]['feinwertungA1'].$tabelleA[$x]['turniere'];
				$tabelleA[$x]['platz'] = ($neu == $alt) ? '' : $platz.'.';
				$alt = $neu;
			}

			// Grand-Prix-Tabelle sortieren
			$tabelleB = $this->sortArrayByFields($teilnehmerliste, array('gesamtpunkteB' => SORT_DESC, 'turniere' => SORT_ASC, 'feinwertungB1' => SORT_DESC));

			// Teilnehmer mit 0 Turnieren und Kat. A entfernen
			$temp = array();
			for($x = 0; $x < count($tabelleB); $x++)
			{
				if($tabelleB[$x]['turniere'] > 0 && $tabelleB[$x]['kategorie'] == 'B')
				{
					$temp[] = $tabelleB[$x];
				}
			}
			$tabelleB = $temp;

			// Plazierung eintragen
			$alt = '';
			for($x = 0; $x < count($tabelleB); $x++)
			{
				$platz = $x + 1;
				$neu = $tabelleB[$x]['punkte'].$tabelleB[$x]['feinwertungB1'].$tabelleB[$x]['turniere'];
				$tabelleB[$x]['platz'] = ($neu == $alt) ? '' : $platz.'.';
				$alt = $neu;
			}

		}

		//$content = "<pre>".print_r($platzreihenfolge, 1).'</pre>';
		//$content .= "<pre>".print_r($platzanzahl, 1).'</pre>';
		$content .= "<pre>".print_r($teilnehmerliste, 1).'</pre>';
		$this->Template->tabelleA = $tabelleA;
		$this->Template->tabelleB = $tabelleB;
		$this->Template->anzahlTurniere = $max_turniernummer;
		return;


		if($objGrandPrix->numRows == 1)
		{

			if($objTurniere->numRows > 0)
			{
				$arrGP = array(); // Array mit den Spielern/Wertungspunkten initialisieren
				$turnier = 0; // Zähler für Turniernummer
				

				// Summenwertung im Grand-Prix-Array berechnen
				for($x = 0; $x < count($arrGP); $x++)
				{
					// Turnieranzahl eintragen
					$anzahl = 0;
					$beste_turniere = array(); // Nimmt die Wertungspunkte der Turniere auf
					if($arrGP[$x]['bonus']) 
					{
						$anzahl++;
						$beste_turniere[] = sprintf("%2s",$arrGP[$x]['bonus']); // zweistellig, z.B. "01" statt "1"
					}
					for($t = 1; $t <= 14; $t++)
					{
						if($arrGP[$x]['t'.$t] !== false) 
						{
							$anzahl++;
							$beste_turniere[] = $arrGP[$x]['t'.$t]; // zweistellig, z.B. "01" statt "1"
						}
					}
					rsort($beste_turniere); // Drittwertung alphabetisch sortieren, höchster Wert am Anfang
					array_push($beste_turniere, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0); // Array auffüllen (damit Maximum der zu wertenden Turniere erreicht wird)
					$beste_turniere = array_slice($beste_turniere, 0, $objGrandPrix->max); // Auf beste x Turniere kürzen
					$arrGP[$x]['punkte'] = array_sum($beste_turniere); // Summe der Wertungen eintragen
					$arrGP[$x]['anzahl'] = $anzahl;
					// 1. Feinwertung eintragen
					$temp = '';
					foreach ($beste_turniere as $element) 
					{
						$temp .= substr('0'.$element, -2).'_';
					}
					$arrGP[$x]['feinwertung1'] = $temp;
				}
				
				// Grand-Prix-Tabelle sortieren
				$arrGP = $this->sortArrayByFields($arrGP, array('punkte' => SORT_DESC, 'feinwertung1' => SORT_DESC, 'anzahl' => SORT_DESC));

				//print_r($arrGP);
				// Header erstellen
				$content = '<table>';
				$content .= '<tr>';
				$content .= '<th>Pl.</th>';
				$content .= '<th>Teilnehmer</th>';
				$content .= '<th>B</th>';
				for($x = 1; $x <= $objTurniere->numRows; $x++)
				{
					$content .= '<th>T'.$x.'</th>';
				}
				$content .= '<th>Summe</th>';
				$content .= '<th>Anz.</th>';
				$content .= '</tr>';
				$alt = '';
				for($x = 0; $x < count($arrGP); $x++)
				{
					$platz = $x + 1;
					$neu = $arrGP[$x]['punkte'].$arrGP[$x]['feinwertung1'].$arrGP[$x]['anzahl'];
					$content .= '<tr>';
					$content .= $neu == $alt ? '<td>&nbsp;</td>' : '<td>'.$platz.'.</td>';
					$alt = $neu;
					$content .= '<td>'.$arrGP[$x]['name'].'</td>';
					$content .= '<td>'.$arrGP[$x]['bonus'].'</td>';
					for($y = 1; $y <= $objTurniere->numRows; $y++)
					{
						$content .= '<td>'.$arrGP[$x]['t'.$y].'</td>';
					}
					$content .= '<td>'.$arrGP[$x]['punkte'].'</td>';
					$content .= '<td>'.$arrGP[$x]['anzahl'].'</td>';
					$content .= '</tr>';
				}
				$content .= '</table>';
				
				//$content .= $objTurniere->title;
			}
			$this->Template->tabelle = $content;
		}
		else
		{
			$this->Template->tabelle = 'Noch kein Gesamtstand verfügbar!';
		}
		//global $objPage,$objArticle;
		//print_r($GLOBALS);
		//echo "ID=".$objPage->id;

		// Parameter zuweisen
			// Template ausgeben
			//$this->Template = new \FrontendTemplate($this->strTemplate);
			//$this->Template->class = "ce_chesstable";
			//$this->Template->tabelle = $content;
	}

	/*********************************************************************
	 * Funktion Wertungspunkte
	 * @param turniernummer = Nummer des Turniers
	 * @param kategorie     = 'A' oder 'B'
	 * @param platz_kat     = Erreichter Platz - 1 in der Kategorie (1. Platz = 0, 2. Platz = 1 usw.)
	 * @param wertung       = Array mit den Wertungspunkten, Index beginnt bei 0 für Platz 1
	 * @return float        = ermittelte Wertungspunkte
	 *********************************************************************/
	protected function Wertungspunkte($turniernummer, $kategorie, $platz_kat, $wertung, $platzreihenfolge, $objGrandPrix)
	{
		//global $platzreihenfolge, $objGrandPrix;

		// *************************************************************************************************
		// $platzreihenfolge = Array[Turniernummer][Kategorie]["Platz"][Index]
		// Der Index (ab 0) stellt die Reihenfolge in der Kategorie dar
		// Zugewiesener Wert: Gesamtplatz ohne Rücksicht auf Kategorien und Gäste
		// *************************************************************************************************
		// $objGrandPrix->punktgleich:
		// true  = Wertungspunkte auf allen gleichen Plätzen teilen
		//         Bsp.: 2 gleiche Plätze, WP 8 und 6, beide Plätze bekommen je 7 Punkte
		// false = Wertungspunkte auf allen gleichen Plätzen nicht teilen, sondern höchste Wertung vergeben
		//         Bsp.: 2 gleiche Plätze, WP 8 und 6, beide Plätze bekommen je 8 Punkte
		// *************************************************************************************************

		// Summenwertungspunkte mit Wertungspunkten für den aktuellen Platz initialisieren
		$wp_summe = 0 + $wertung[$platz_kat];
		$wp_max = 0 + $wertung[$platz_kat]; // Maximal mögliche Wertungspunkte initialisieren
		$wp_count = 1; // Gleiche Plazierungen auf 1 setzen
		//echo "Turnier: $turniernummer / Kategorie: $kategorie / Platz: $platz_kat<br>";

		// In Array $platzreihenfolge nach höher plazierten Spielern suchen, die den gleichen Gesamtplatz haben
		for($x = $platz_kat - 1; $x > -1; $x--)
		{
			//echo "... Untersuche aufwärts Platz $x<br>";
			if($platzreihenfolge[$turniernummer][$kategorie]['Platz'][$x] == $platzreihenfolge[$turniernummer][$kategorie]['Platz'][$platz_kat])
			{
				//echo "... Treffer<br>";
				// Untersuchter vorheriger Platz identisch mit Platz des aktuellen Spielers
				$wp_summe += $wertung[$x]; // Wertungspunkte aufaddieren
				$wp_max = $wertung[$x]; // Neue Höchstwertung festlegen
				$wp_count++; // Eine weitere gleiche Plazierung addieren
			}
		}

		// In Array $platzreihenfolge nach niedriger plazierten Spielern suchen, die den gleichen Gesamtplatz haben
		for($x = $platz_kat + 1; $x < count($platzreihenfolge[$turniernummer][$kategorie]['Platz']); $x++)
		{
			//echo "... Untersuche abwärts Platz $x<br>";
			if($platzreihenfolge[$turniernummer][$kategorie]['Platz'][$x] == $platzreihenfolge[$turniernummer][$kategorie]['Platz'][$platz_kat])
			{
				//echo "... Treffer<br>";
				// Untersuchter nächster Platz identisch mit Platz des aktuellen Spielers
				$wp_summe += $wertung[$x]; // Wertungspunkte aufaddieren
				$wp_count++; // Eine weitere gleiche Plazierung addieren
			}
		}

		//echo "--- Summe WP: $wp_summe | Max WP: $wp_max | Zähler WP: $wp_count<br>";
		
		if($objGrandPrix->punktgleich) return 0 + sprintf('%1.2f', $wp_summe / $wp_count);
		else return 0 + $wp_max;
	}


	/*********************************************************************
	 * Funktion TurnierErlaubt
	 * @param nummer    = zu untersuchende Turniernummer
	 * @param excluded  = String mit den nicht erlaubten Turniernummern, z.B. 1,2,4-8
	 * @return boolean  = true/false
	 *********************************************************************/
	protected function TurnierErlaubt($nummer, $excluded)
	{
		$turnierArr = $this->ArrayAufloesen(explode(',', $excluded)); // String z.B. 1,2,4-8 umwandeln in Array(1,2,4,5,6,7,8)
		if(in_array($nummer, $turnierArr)) return false;
		else return true;
	}

	/**
	 * Funktion ArrayAufloesen
	 * @param     array           Bsp.: array('1','3-7','8-9','34')
	 * @return    array           Bsp.: array('1','3','4','5','6','7','8','9','34')
	 */
	protected function ArrayAufloesen($array)
	{
		$newArray = array();
		foreach($array as $item)
		{
			if(ctype_digit($item))
			{
				// Integerzahl direkt übernehmen
				$newArray[] = $item;
			}
			else
			{
				// String in der Form "Zahl-Zahl" auflösen
				$temp = explode("-", $item);
				for($x = $temp[0]; $x <= $temp[1]; $x++)
				{
					$newArray[] = $x;
				}
			}
		}
		return $newArray;
	}

	protected function NameDrehen($intext)
	{
		// Konvertiert Namen der Form Nachname,Vorname,Titel nach Titel Vorname Name
		$array = explode(",",$intext);
		$teile = count($array);
		$result = "";
		for($x=$teile-1;$x>=0;$x--)
		{
			$result .= " ".$array[$x];
		}
		return $result;
	}

	protected function NameKonvertieren($string) 
	{
		// Berichtigt einen String "Name,Vorname,Titel"
		// Entfernung von Leerzeichen und FIDE-Titeln
		$teil = explode(",",$string);
		// Leerzeichen davor und dahinter entfernen
		for($x=0;$x<count($teil);$x++) 
		{
			$teil[$x] = trim($teil[$x]);
		}
		// Neuen String bauen
		$temp = $teil[0];
		for($x=1;$x<count($teil);$x++) 
		{
			if(strtoupper($teil[$x])!="FM" && strtoupper($teil[$x])!="IM" && strtoupper($teil[$x])!="GM" && strtoupper($teil[$x])!="CM" && strtoupper($teil[$x])!="WGM" && strtoupper($teil[$x])!="WIM" && strtoupper($teil[$x])!="WFM" && strtoupper($teil[$x])!="WCM") 
			{
				$temp .= ",".$teil[$x];
			}
		}
		return $temp;
	}

	protected function sortArrayByFields($arr, $fields)
	{
		$sortFields = array();
		$args       = array();
		
		foreach ($arr as $key => $row) {
			foreach ($fields as $field => $order) {
				$sortFields[$field][$key] = $row[$field];
			}
		}
		
		foreach ($fields as $field => $order) {
			$args[] = $sortFields[$field];
			
			if (is_array($order)) {
				foreach ($order as $pt) {
				    $args[$pt];
				}
			} else {
				$args[] = $order;
			}
		}
		
		$args[] = &$arr;
		
		call_user_func_array('array_multisort', $args);
		
		return $arr;
	}

}
