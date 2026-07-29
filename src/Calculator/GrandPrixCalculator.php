<?php

declare(strict_types=1);

/*
 * This file is part of schachbulle/contao-berolinagrandprix-bundle.
 *
 * (c) Frank Hoppe
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoBerolinaGrandPrixBundle\Calculator;

/**
 * Berechnet die Grand-Prix-Tabellen der Kategorien A und B.
 *
 * Die Klasse ist bewusst frei von Contao-Abhängigkeiten, damit sie ohne
 * Framework-Bootstrap getestet werden kann. Sie erhält die bereits geladenen
 * Rohdaten (Teilnehmer und Turnier-CSV) und liefert die fertig sortierten
 * Tabellen zurück.
 */
class GrandPrixCalculator
{
	/**
	 * Wertungsarten für die Reihenfolge der Feinwertung.
	 */
	public const ORDER_POINTS = 1;          // Höhere Grand-Prix-Punkte
	public const ORDER_TOURNAMENTS_DESC = 2; // Höhere Anzahl gespielter Turniere
	public const ORDER_TOURNAMENTS_ASC = 3;  // Niedrigere Anzahl gespielter Turniere
	public const ORDER_WINS = 4;             // Höhere Anzahl gewonnener Turniere

	/**
	 * DWZ-Grenze: Teilnehmer oberhalb dieser Grenze gehören zur Kategorie A.
	 */
	private $maxDwz;

	/**
	 * Anzahl der besten Turniere, die in die Wertung eingehen.
	 */
	private $bestOf;

	/**
	 * Wertungspunkte bei Platzgleichheit teilen?
	 */
	private $sharePoints;

	/**
	 * Wertungspunkte der Kategorie A, Index 0 = 1. Platz.
	 *
	 * @var array<int, float>
	 */
	private $ratingA;

	/**
	 * Wertungspunkte der Kategorie B, Index 0 = 1. Platz.
	 *
	 * @var array<int, float>
	 */
	private $ratingB;

	/**
	 * Reihenfolge der Wertungskriterien (Konstanten ORDER_*).
	 *
	 * @var array<int, int>
	 */
	private $evaluationOrder;

	/**
	 * Teilnehmer ohne Wertungspunkte anzeigen?
	 */
	private $showZeroScores;

	/**
	 * @param string             $ratingA         Kommagetrennte Wertungspunkte der Kategorie A
	 * @param string             $ratingB         Kommagetrennte Wertungspunkte der Kategorie B
	 * @param array<int, scalar> $evaluationOrder Reihenfolge der Wertungskriterien
	 */
	public function __construct(int $maxDwz, int $bestOf, bool $sharePoints, string $ratingA, string $ratingB, array $evaluationOrder = array(), bool $showZeroScores = true)
	{
		$this->maxDwz = $maxDwz;
		$this->bestOf = max(0, $bestOf);
		$this->sharePoints = $sharePoints;
		$this->ratingA = $this->parseRating($ratingA);
		$this->ratingB = $this->parseRating($ratingB);
		$this->showZeroScores = $showZeroScores;

		$this->evaluationOrder = array();

		foreach ($evaluationOrder as $intOrder)
		{
			$intOrder = (int) $intOrder;

			if (\in_array($intOrder, array(self::ORDER_POINTS, self::ORDER_TOURNAMENTS_DESC, self::ORDER_TOURNAMENTS_ASC, self::ORDER_WINS), true))
			{
				$this->evaluationOrder[] = $intOrder;
			}
		}
	}

	/**
	 * Die Tabellen der Kategorien A und B berechnen.
	 *
	 * @param array<int, array<string, mixed>> $players     Teilnehmer mit den Schlüsseln playername, playerdwz, excluded
	 * @param array<int, string>               $tournaments Turnier-CSV in Reihenfolge der Austragung
	 * @param int                              $count       Höchste zu berücksichtigende Turniernummer
	 *
	 * @return array{A: array<int, array<string, mixed>>, B: array<int, array<string, mixed>>}
	 */
	public function calculate(array $players, array $tournaments, int $count): array
	{
		$count = max(0, $count);
		$participants = $this->initParticipants($players, $count);

		// Namensregister für den direkten Zugriff (statt linearer Suche je CSV-Zeile)
		// und die je Teilnehmer ausgeschlossenen Turniere einmalig vorberechnen
		$index = array();
		$excluded = array();

		foreach ($participants as $intKey => $arrParticipant)
		{
			$strName = $arrParticipant['playername'];

			if ('' !== $strName && !isset($index[$strName]))
			{
				$index[$strName] = $intKey;
			}

			$excluded[$intKey] = $this->expandRanges($arrParticipant['excluded']);
		}

		// Plazierungen aus den Turnier-CSV übernehmen
		$order = $this->collectPlacements($participants, $index, $excluded, $tournaments, $count);

		// Plazierungen in Wertungspunkte umrechnen
		$this->applyRatingPoints($participants, $order, $count);

		// Gesamtpunkte und Streichwertungen ermitteln
		$this->applyTotals($participants);

		return array
		(
			'A' => $this->buildTable($participants, 'A'),
			'B' => $this->buildTable($participants, 'B'),
		);
	}

	/**
	 * Kommagetrennte Wertungspunkte in ein Float-Array umwandeln.
	 *
	 * @return array<int, float>
	 */
	private function parseRating(string $strRating): array
	{
		$arrRating = array();

		foreach (explode(',', $strRating) as $strValue)
		{
			$arrRating[] = (float) str_replace(',', '.', trim($strValue));
		}

		return $arrRating;
	}

	/**
	 * Teilnehmerliste um die Auswertungsfelder erweitern.
	 *
	 * @param array<int, array<string, mixed>> $players
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function initParticipants(array $players, int $count): array
	{
		$participants = array();

		foreach (array_values($players) as $arrPlayer)
		{
			$intDwz = (int) ($arrPlayer['playerdwz'] ?? 0);

			$arrParticipant = $arrPlayer;
			$arrParticipant['playername'] = trim((string) ($arrPlayer['playername'] ?? ''));
			$arrParticipant['playerdwz'] = $arrPlayer['playerdwz'] ?? '';
			$arrParticipant['excluded'] = (string) ($arrPlayer['excluded'] ?? '');
			$arrParticipant['kategorie'] = $intDwz > $this->maxDwz ? 'A' : 'B';
			$arrParticipant['turniere'] = 0;
			$arrParticipant['turniersiegeA'] = 0;
			$arrParticipant['turniersiegeB'] = 0;
			$arrParticipant['gesamtpunkteA'] = 0.0;
			$arrParticipant['gesamtpunkteB'] = 0.0;
			$arrParticipant['turnierpunkteA'] = array();
			$arrParticipant['turnierpunkteB'] = array();
			$arrParticipant['platz'] = '';

			for ($t = 1; $t <= $count; ++$t)
			{
				$arrParticipant['turnierpunkteA'][$t] = false;
				$arrParticipant['turnierpunkteB'][$t] = false;
			}

			$participants[] = $arrParticipant;
		}

		return $participants;
	}

	/**
	 * Die Plazierungen aller Turniere einlesen.
	 *
	 * Liefert je Turnier und Kategorie die Rangfolge zurück:
	 * $order[Turnier][Kategorie]['Platz'|'Spieler'][Index]
	 *
	 * @param array<int, array<string, mixed>> $participants
	 * @param array<string, int>               $index
	 * @param array<int, array<int, int>>      $excluded
	 * @param array<int, string>               $tournaments
	 *
	 * @return array<int, array<string, array<string, array<int, int>>>>
	 */
	private function collectPlacements(array &$participants, array $index, array $excluded, array $tournaments, int $count): array
	{
		$order = array();
		$intTournament = 0;

		foreach (array_values($tournaments) as $strCsv)
		{
			if (++$intTournament > $count)
			{
				break;
			}

			// Zeilenumbrüche aller Betriebssysteme berücksichtigen, erste Zeile ist die Kopfzeile
			$arrRows = preg_split('/\r\n|\n|\r/', (string) $strCsv) ?: array();
			array_shift($arrRows);

			foreach ($arrRows as $strRow)
			{
				if ('' === trim($strRow))
				{
					continue;
				}

				$arrColumns = explode(';', $strRow);

				// Platz: führende Ziffern, z.B. "1" oder "1."
				if (!preg_match('/^\s*(\d+)/', $arrColumns[0], $arrMatch))
				{
					continue;
				}

				$intPlace = (int) $arrMatch[1];
				$strName = trim((string) ($arrColumns[1] ?? ''));

				if ('' === $strName || !isset($index[$strName]))
				{
					continue;
				}

				$intPlayer = $index[$strName];

				// Prüfen, ob das Turnier bei diesem Teilnehmer gewertet werden darf
				if (\in_array($intTournament, $excluded[$intPlayer], true))
				{
					continue;
				}

				++$participants[$intPlayer]['turniere'];

				// Teilnehmer der Kategorie B werden zusätzlich in der Gesamtwertung A geführt
				$participants[$intPlayer]['turnierpunkteA'][$intTournament] = $intPlace;
				$order[$intTournament]['A']['Platz'][] = $intPlace;
				$order[$intTournament]['A']['Spieler'][] = $intPlayer;

				if ('B' === $participants[$intPlayer]['kategorie'])
				{
					$participants[$intPlayer]['turnierpunkteB'][$intTournament] = $intPlace;
					$order[$intTournament]['B']['Platz'][] = $intPlace;
					$order[$intTournament]['B']['Spieler'][] = $intPlayer;
				}
			}
		}

		return $order;
	}

	/**
	 * Plazierungen durch die zugehörigen Wertungspunkte ersetzen.
	 *
	 * @param array<int, array<string, mixed>>                          $participants
	 * @param array<int, array<string, array<string, array<int, int>>>> $order
	 */
	private function applyRatingPoints(array &$participants, array $order, int $count): void
	{
		$arrCategories = array('A' => $this->ratingA, 'B' => $this->ratingB);

		for ($t = 1; $t <= $count; ++$t)
		{
			foreach ($arrCategories as $strCategory => $arrRating)
			{
				if (!isset($order[$t][$strCategory]['Platz']))
				{
					continue;
				}

				$arrPlaces = $order[$t][$strCategory]['Platz'];
				$strField = 'turnierpunkte' . $strCategory;
				$strWins = 'turniersiege' . $strCategory;

				foreach ($arrPlaces as $intPosition => $intPlace)
				{
					$intPlayer = $order[$t][$strCategory]['Spieler'][$intPosition];
					$fltPoints = $this->getRatingPoints($arrPlaces, $intPosition, $arrRating);

					$participants[$intPlayer][$strField][$t] = $fltPoints;

					if (isset($arrRating[0]) && $fltPoints === $arrRating[0])
					{
						++$participants[$intPlayer][$strWins];
					}
				}
			}
		}
	}

	/**
	 * Die Wertungspunkte für eine Plazierung ermitteln.
	 *
	 * Bei Punktgleichheit werden die Wertungspunkte aller gleichen Plätze
	 * entweder geteilt (sharePoints = true) oder es wird die höchste Wertung
	 * an alle vergeben (sharePoints = false).
	 *
	 * @param array<int, int>   $places   Gesamtplätze in der Rangfolge der Kategorie
	 * @param int               $position Index des zu bewertenden Teilnehmers
	 * @param array<int, float> $rating   Wertungspunkte, Index 0 = 1. Platz
	 */
	private function getRatingPoints(array $places, int $position, array $rating): float
	{
		$fltSum = $rating[$position] ?? 0.0;
		$fltMax = $rating[$position] ?? 0.0;
		$intCount = 1;

		// Höher plazierte Teilnehmer mit gleichem Gesamtplatz suchen
		for ($x = $position - 1; $x >= 0; --$x)
		{
			if ($places[$x] !== $places[$position])
			{
				continue;
			}

			$fltSum += $rating[$x] ?? 0.0;
			$fltMax = $rating[$x] ?? 0.0; // Die höchste Wertung steht am weitesten oben
			++$intCount;
		}

		// Niedriger plazierte Teilnehmer mit gleichem Gesamtplatz suchen
		for ($x = $position + 1, $intMax = \count($places); $x < $intMax; ++$x)
		{
			if ($places[$x] !== $places[$position])
			{
				continue;
			}

			$fltSum += $rating[$x] ?? 0.0;
			++$intCount;
		}

		return $this->sharePoints ? round($fltSum / $intCount, 2) : $fltMax;
	}

	/**
	 * Gesamtpunkte je Kategorie bilden und Streichwertungen kennzeichnen.
	 *
	 * @param array<int, array<string, mixed>> $participants
	 */
	private function applyTotals(array &$participants): void
	{
		foreach ($participants as &$arrParticipant)
		{
			foreach (array('A', 'B') as $strCategory)
			{
				$strField = 'turnierpunkte' . $strCategory;

				// Die besten x Turniere bestimmen (Schlüssel bleiben erhalten)
				$arrBest = $arrParticipant[$strField];
				arsort($arrBest);
				$arrBest = array_slice($arrBest, 0, $this->bestOf, true);

				$arrParticipant['gesamtpunkte' . $strCategory] = array_sum($arrBest);

				// Feinwertung aus den besten x Turnieren (absteigend, zweistellig)
				$strFine = '';

				foreach ($arrBest as $fltValue)
				{
					$strFine .= sprintf('%02d_', (int) $fltValue);
				}

				$arrParticipant['feinwertung' . $strCategory . '1'] = $strFine;

				// Nicht gewertete Turniere durchstreichen - aber nur solche, an
				// denen der Teilnehmer auch teilgenommen hat (false = nicht gespielt)
				if ($arrParticipant['turniere'] <= $this->bestOf)
				{
					continue;
				}

				foreach ($arrParticipant[$strField] as $intKey => $varValue)
				{
					if (false !== $varValue && !isset($arrBest[$intKey]))
					{
						$arrParticipant[$strField][$intKey] = '<s>' . $varValue . '</s>';
					}
				}
			}
		}

		unset($arrParticipant);
	}

	/**
	 * Eine Kategorie-Tabelle sortieren, filtern und die Plazierung eintragen.
	 *
	 * @param array<int, array<string, mixed>> $participants
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function buildTable(array $participants, string $strCategory): array
	{
		$arrTable = $this->sortByEvaluationOrder($participants, $strCategory);

		// Teilnehmer ohne gespielte Turniere entfernen, in Kategorie B
		// zusätzlich alle Teilnehmer der Kategorie A
		$arrFiltered = array();

		foreach ($arrTable as $arrRow)
		{
			if ($arrRow['turniere'] < 1)
			{
				continue;
			}

			if ('B' === $strCategory && 'B' !== $arrRow['kategorie'])
			{
				continue;
			}

			if (!$this->showZeroScores && $arrRow['gesamtpunkte' . $strCategory] <= 0)
			{
				continue;
			}

			$arrFiltered[] = $arrRow;
		}

		// Plazierung eintragen - bei identischer Wertung derselbe Platz
		$arrPrevious = null;

		foreach ($arrFiltered as $intKey => $arrRow)
		{
			$arrCurrent = array();

			foreach ($this->evaluationOrder as $intOrder)
			{
				$arrCurrent[] = $arrRow[$this->getOrderField($intOrder, $strCategory)];
			}

			$arrFiltered[$intKey]['platz'] = $arrCurrent === $arrPrevious ? '' : ($intKey + 1) . '.';
			$arrPrevious = $arrCurrent;
		}

		return $arrFiltered;
	}

	/**
	 * Die Teilnehmer nach der konfigurierten Wertungsreihenfolge sortieren.
	 *
	 * @param array<int, array<string, mixed>> $participants
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function sortByEvaluationOrder(array $participants, string $strCategory): array
	{
		if (!$this->evaluationOrder || !$participants)
		{
			// Ohne Wertungsreihenfolge bleibt die Eingabereihenfolge erhalten
			return $participants;
		}

		$arrArgs = array();

		foreach ($this->evaluationOrder as $intOrder)
		{
			$strField = $this->getOrderField($intOrder, $strCategory);

			$arrArgs[] = array_column($participants, $strField);
			$arrArgs[] = self::ORDER_TOURNAMENTS_ASC === $intOrder ? SORT_ASC : SORT_DESC;
			$arrArgs[] = SORT_NUMERIC;
		}

		$arrArgs[] = &$participants;

		array_multisort(...$arrArgs);

		return $participants;
	}

	/**
	 * Das Datenfeld zu einem Wertungskriterium ermitteln.
	 */
	private function getOrderField(int $intOrder, string $strCategory): string
	{
		switch ($intOrder)
		{
			case self::ORDER_POINTS:
				return 'gesamtpunkte' . $strCategory;

			case self::ORDER_WINS:
				return 'turniersiege' . $strCategory;

			default:
				return 'turniere';
		}
	}

	/**
	 * Eine Nummernliste mit Bereichen auflösen.
	 *
	 * Beispiel: "1,3-7,34" ergibt array(1, 3, 4, 5, 6, 7, 34)
	 *
	 * @return array<int, int>
	 */
	private function expandRanges(string $strList): array
	{
		if ('' === trim($strList))
		{
			return array();
		}

		$arrNumbers = array();

		foreach (explode(',', $strList) as $strItem)
		{
			$strItem = trim($strItem);

			if (ctype_digit($strItem))
			{
				$arrNumbers[] = (int) $strItem;
				continue;
			}

			// Bereich in der Form "Zahl-Zahl" auflösen
			if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $strItem, $arrMatch))
			{
				$intFrom = (int) $arrMatch[1];
				$intTo = (int) $arrMatch[2];

				for ($x = $intFrom; $x <= $intTo; ++$x)
				{
					$arrNumbers[] = $x;
				}
			}
		}

		return $arrNumbers;
	}
}
