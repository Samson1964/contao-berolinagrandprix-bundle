<?php

declare(strict_types=1);

/*
 * This file is part of schachbulle/contao-berolinagrandprix-bundle.
 *
 * (c) Frank Hoppe
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoBerolinaGrandPrixBundle\Tests\Calculator;

use PHPUnit\Framework\TestCase;
use Schachbulle\ContaoBerolinaGrandPrixBundle\Calculator\GrandPrixCalculator;

class GrandPrixCalculatorTest extends TestCase
{
	/**
	 * Standard-Teilnehmerfeld: zwei Spieler der Kategorie A, zwei der Kategorie B.
	 */
	private function getPlayers(): array
	{
		return array
		(
			array('playername' => 'Anders,Anna', 'playerdwz' => '2100', 'excluded' => ''),
			array('playername' => 'Berger,Bert', 'playerdwz' => '1900', 'excluded' => ''),
			array('playername' => 'Claus,Carl', 'playerdwz' => '1500', 'excluded' => ''),
			array('playername' => 'Dorn,Dora', 'playerdwz' => '1200', 'excluded' => ''),
		);
	}

	private function getCalculator(array $order = array(1), bool $sharePoints = true, bool $showZero = true, int $bestOf = 5): GrandPrixCalculator
	{
		return new GrandPrixCalculator(1799, $bestOf, $sharePoints, '10,8,6,5,4,3,2,1', '5,3,2,1', $order, $showZero);
	}

	/**
	 * Eine Tabellenzeile anhand des Namens holen.
	 */
	private function getRow(array $arrTable, string $strName): array
	{
		foreach ($arrTable as $arrRow)
		{
			if ($strName === $arrRow['playername'])
			{
				return $arrRow;
			}
		}

		$this->fail(sprintf('Der Teilnehmer "%s" fehlt in der Tabelle.', $strName));
	}

	public function testKategorieWirdAnhandDerDwzGrenzeVergeben(): void
	{
		$csv = "Platz;Name\n1;Anders,Anna\n2;Claus,Carl\n";

		$result = $this->getCalculator()->calculate($this->getPlayers(), array($csv), 1);

		$arrCategories = array();

		foreach ($result['A'] as $arrRow)
		{
			$arrCategories[$arrRow['playername']] = $arrRow['kategorie'];
		}

		$this->assertSame('A', $arrCategories['Anders,Anna'], 'DWZ 2100 liegt über der Grenze 1799');
		$this->assertSame('B', $arrCategories['Claus,Carl'], 'DWZ 1500 liegt unter der Grenze 1799');
	}

	public function testTeilnehmerDerKategorieBErscheinenInBeidenTabellen(): void
	{
		$csv = "Platz;Name\n1;Anders,Anna\n2;Claus,Carl\n";

		$result = $this->getCalculator()->calculate($this->getPlayers(), array($csv), 1);

		$this->assertCount(2, $result['A'], 'Die Gesamtwertung enthält beide Teilnehmer');
		$this->assertCount(1, $result['B'], 'Die B-Wertung enthält nur den B-Teilnehmer');
		$this->assertSame('Claus,Carl', $result['B'][0]['playername']);
	}

	public function testWertungspunkteEntsprechenDerPlazierung(): void
	{
		$csv = "Platz;Name\n1;Anders,Anna\n2;Berger,Bert\n";

		$result = $this->getCalculator()->calculate($this->getPlayers(), array($csv), 1);

		$this->assertSame(10.0, $result['A'][0]['gesamtpunkteA'], '1. Platz ergibt 10 Punkte');
		$this->assertSame(8.0, $result['A'][1]['gesamtpunkteA'], '2. Platz ergibt 8 Punkte');
	}

	public function testPunkteWerdenBeiPlatzgleichheitGeteilt(): void
	{
		// Beide Teilnehmer belegen Platz 1: (10 + 8) / 2 = 9
		$csv = "Platz;Name\n1;Anders,Anna\n1;Berger,Bert\n";

		$result = $this->getCalculator(array(1), true)->calculate($this->getPlayers(), array($csv), 1);

		$this->assertSame(9.0, $result['A'][0]['gesamtpunkteA']);
		$this->assertSame(9.0, $result['A'][1]['gesamtpunkteA']);
	}

	public function testOhneTeilungErhaltenAlleDieHoechsteWertung(): void
	{
		$csv = "Platz;Name\n1;Anders,Anna\n1;Berger,Bert\n";

		$result = $this->getCalculator(array(1), false)->calculate($this->getPlayers(), array($csv), 1);

		$this->assertSame(10.0, $result['A'][0]['gesamtpunkteA']);
		$this->assertSame(10.0, $result['A'][1]['gesamtpunkteA']);
	}

	public function testGleicheWertungErgibtGleichenPlatz(): void
	{
		$csv = "Platz;Name\n1;Anders,Anna\n1;Berger,Bert\n";

		$result = $this->getCalculator(array(1), false)->calculate($this->getPlayers(), array($csv), 1);

		$this->assertSame('1.', $result['A'][0]['platz']);
		$this->assertSame('', $result['A'][1]['platz'], 'Bei gleicher Wertung bleibt die Platzziffer leer');
	}

	public function testLeereUndUnvollstaendigeCsvZeilenWerdenUebersprungen(): void
	{
		// Enthält eine Leerzeile, eine Zeile ohne Platzziffer und eine Zeile ohne Namen
		$csv = "Platz;Name\n1;Anders,Anna\n\n;Berger,Bert\n2;\nx;Dorn,Dora\n";

		$result = $this->getCalculator()->calculate($this->getPlayers(), array($csv), 1);

		$this->assertCount(1, $result['A'], 'Nur die gültige Zeile wird gewertet');
		$this->assertSame('Anders,Anna', $result['A'][0]['playername']);
	}

	public function testCarriageReturnZeilenumbruecheWerdenUnterstuetzt(): void
	{
		$csv = "Platz;Name\r\n1;Anders,Anna\r\n2;Berger,Bert\r\n";

		$result = $this->getCalculator()->calculate($this->getPlayers(), array($csv), 1);

		$this->assertCount(2, $result['A']);
		$this->assertSame(10.0, $result['A'][0]['gesamtpunkteA']);
	}

	public function testPlatzMitPunktWirdErkannt(): void
	{
		$csv = "Platz;Name\n1.;Anders,Anna\n2.;Berger,Bert\n";

		$result = $this->getCalculator()->calculate($this->getPlayers(), array($csv), 1);

		$this->assertSame(10.0, $result['A'][0]['gesamtpunkteA']);
		$this->assertSame(8.0, $result['A'][1]['gesamtpunkteA']);
	}

	public function testAusgeschlosseneTurniereWerdenNichtGewertet(): void
	{
		$players = $this->getPlayers();
		$players[0]['excluded'] = '2-3';

		$csv1 = "Platz;Name\n1;Anders,Anna\n";
		$csv2 = "Platz;Name\n1;Anders,Anna\n";
		$csv3 = "Platz;Name\n1;Anders,Anna\n";

		$result = $this->getCalculator()->calculate($players, array($csv1, $csv2, $csv3), 3);

		$this->assertSame(1, $result['A'][0]['turniere'], 'Turnier 2 und 3 sind ausgeschlossen');
		$this->assertSame(10.0, $result['A'][0]['gesamtpunkteA']);
	}

	public function testWertungspunkteRichtenSichNachDemRangInDerKategorie(): void
	{
		// Anna ist die einzige gewertete Teilnehmerin und steht damit trotz
		// Gesamtplatz 3 auf Rang 1 ihrer Kategorie.
		$csv = "Platz;Name\n3;Anders,Anna\n";

		$result = $this->getCalculator()->calculate($this->getPlayers(), array($csv), 1);

		$this->assertSame(10.0, $result['A'][0]['gesamtpunkteA']);
	}

	public function testNurDieBestenTurniereWerdenGewertet(): void
	{
		// Anna belegt in ihrer Kategorie Rang 1, 2 und 3: beste 2 von 3 = 10 + 8 = 18
		$csv1 = "Platz;Name\n1;Anders,Anna\n2;Berger,Bert\n";
		$csv2 = "Platz;Name\n1;Berger,Bert\n2;Anders,Anna\n";
		$csv3 = "Platz;Name\n1;Berger,Bert\n2;Claus,Carl\n3;Anders,Anna\n";

		$result = $this->getCalculator(array(1), true, true, 2)->calculate($this->getPlayers(), array($csv1, $csv2, $csv3), 3);

		$arrAnna = $this->getRow($result['A'], 'Anders,Anna');

		$this->assertSame(18.0, $arrAnna['gesamtpunkteA']);
		$this->assertSame(3, $arrAnna['turniere']);
	}

	public function testStreichwertungenWerdenDurchgestrichen(): void
	{
		$csv1 = "Platz;Name\n1;Anders,Anna\n2;Berger,Bert\n";
		$csv2 = "Platz;Name\n1;Berger,Bert\n2;Anders,Anna\n";
		$csv3 = "Platz;Name\n1;Berger,Bert\n2;Claus,Carl\n3;Anders,Anna\n";

		$result = $this->getCalculator(array(1), true, true, 2)->calculate($this->getPlayers(), array($csv1, $csv2, $csv3), 3);

		$arrPoints = $this->getRow($result['A'], 'Anders,Anna')['turnierpunkteA'];

		$this->assertSame(10.0, $arrPoints[1]);
		$this->assertSame(8.0, $arrPoints[2]);
		$this->assertSame('<s>6</s>', $arrPoints[3], 'Das schlechteste Turnier wird gestrichen');
	}

	public function testNichtGespielteTurniereWerdenNichtDurchgestrichen(): void
	{
		// Vier Turniere, gespielt wurden nur die ersten drei, gewertet werden zwei
		$csv1 = "Platz;Name\n1;Anders,Anna\n";
		$csv2 = "Platz;Name\n2;Anders,Anna\n";
		$csv3 = "Platz;Name\n3;Anders,Anna\n";
		$csv4 = "Platz;Name\n1;Berger,Bert\n";

		$result = $this->getCalculator(array(1), true, true, 2)->calculate($this->getPlayers(), array($csv1, $csv2, $csv3, $csv4), 4);

		$arrPoints = $result['A'][0]['turnierpunkteA'];

		$this->assertSame('Anders,Anna', $result['A'][0]['playername']);
		$this->assertFalse($arrPoints[4], 'Nicht gespielte Turniere bleiben leer statt "<s></s>"');
	}

	public function testTurniersiegeWerdenGezaehlt(): void
	{
		$csv1 = "Platz;Name\n1;Anders,Anna\n2;Berger,Bert\n";
		$csv2 = "Platz;Name\n1;Anders,Anna\n2;Berger,Bert\n";

		$result = $this->getCalculator()->calculate($this->getPlayers(), array($csv1, $csv2), 2);

		$this->assertSame(2, $result['A'][0]['turniersiegeA']);
		$this->assertSame(0, $result['A'][1]['turniersiegeA']);
	}

	public function testSortierungNachAnzahlDerTurniere(): void
	{
		// Bert spielt zwei Turniere, Anna nur eines - trotz höherer Punktzahl
		$csv1 = "Platz;Name\n1;Anders,Anna\n2;Berger,Bert\n";
		$csv2 = "Platz;Name\n1;Berger,Bert\n";

		$result = $this->getCalculator(array(2))->calculate($this->getPlayers(), array($csv1, $csv2), 2);

		$this->assertSame('Berger,Bert', $result['A'][0]['playername']);
		$this->assertSame(2, $result['A'][0]['turniere']);
	}

	public function testOhneWertungsreihenfolgeBleibtDieReihenfolgeErhalten(): void
	{
		$csv = "Platz;Name\n2;Anders,Anna\n1;Berger,Bert\n";

		$result = $this->getCalculator(array())->calculate($this->getPlayers(), array($csv), 1);

		$this->assertCount(2, $result['A'], 'Ohne Wertungsreihenfolge gehen keine Datensätze verloren');
		$this->assertSame('Anders,Anna', $result['A'][0]['playername'], 'Die Eingabereihenfolge bleibt erhalten');
		$this->assertSame('1.', $result['A'][0]['platz']);
	}

	public function testTeilnehmerOhneTurniereWerdenEntfernt(): void
	{
		$csv = "Platz;Name\n1;Anders,Anna\n";

		$result = $this->getCalculator()->calculate($this->getPlayers(), array($csv), 1);

		$this->assertCount(1, $result['A']);
		$this->assertSame('Anders,Anna', $result['A'][0]['playername']);
	}

	public function testNullwertungenKoennenAusgeblendetWerden(): void
	{
		// Nur ein Wertungsplatz, der zweite Teilnehmer erhält 0 Punkte
		$calculator = new GrandPrixCalculator(1799, 5, true, '10', '5', array(1), false);
		$csv = "Platz;Name\n1;Anders,Anna\n2;Berger,Bert\n";

		$result = $calculator->calculate($this->getPlayers(), array($csv), 1);

		$this->assertCount(1, $result['A'], 'Der Teilnehmer mit 0 Punkten wird ausgeblendet');
		$this->assertSame('Anders,Anna', $result['A'][0]['playername']);
	}

	public function testNullwertungenWerdenStandardmaessigAngezeigt(): void
	{
		$calculator = new GrandPrixCalculator(1799, 5, true, '10', '5', array(1), true);
		$csv = "Platz;Name\n1;Anders,Anna\n2;Berger,Bert\n";

		$result = $calculator->calculate($this->getPlayers(), array($csv), 1);

		$this->assertCount(2, $result['A']);
	}

	public function testUnbekannteNamenWerdenIgnoriert(): void
	{
		$csv = "Platz;Name\n1;Fremd,Frank\n2;Anders,Anna\n";

		$result = $this->getCalculator()->calculate($this->getPlayers(), array($csv), 1);

		$this->assertCount(1, $result['A']);
		$this->assertSame(10.0, $result['A'][0]['gesamtpunkteA'], 'Gäste belegen keinen Wertungsrang, Anna rückt auf Rang 1 vor');
	}

	public function testMehrTurniereAlsErlaubtWerdenIgnoriert(): void
	{
		$csv1 = "Platz;Name\n1;Anders,Anna\n";
		$csv2 = "Platz;Name\n1;Anders,Anna\n";

		$result = $this->getCalculator()->calculate($this->getPlayers(), array($csv1, $csv2), 1);

		$this->assertSame(1, $result['A'][0]['turniere']);
	}

	public function testLeereTeilnehmerlisteErzeugtLeereTabellen(): void
	{
		$result = $this->getCalculator()->calculate(array(), array("Platz;Name\n1;Anders,Anna\n"), 1);

		$this->assertSame(array(), $result['A']);
		$this->assertSame(array(), $result['B']);
	}

	public function testFehlerhafteWertungspunkteFuehrenNichtZumAbbruch(): void
	{
		// Leere und nicht numerische Werte in der Wertungsliste
		$calculator = new GrandPrixCalculator(1799, 5, true, '10,,abc,5', '5', array(1), true);
		$csv = "Platz;Name\n1;Anders,Anna\n2;Berger,Bert\n";

		$result = $calculator->calculate($this->getPlayers(), array($csv), 1);

		$this->assertSame(10.0, $result['A'][0]['gesamtpunkteA']);
		$this->assertSame(0.0, $result['A'][1]['gesamtpunkteA']);
	}

	public function testFehlendeSpaltenInDerTeilnehmerlisteFuehrenNichtZumAbbruch(): void
	{
		// Zeilen aus dem MultiColumnWizard können unvollständig sein
		$players = array(
			array('playername' => 'Anders,Anna'),
			array('playerdwz' => '1500'),
			array(),
		);

		$csv = "Platz;Name\n1;Anders,Anna\n";

		$result = $this->getCalculator()->calculate($players, array($csv), 1);

		$this->assertCount(1, $result['A']);
		$this->assertSame(10.0, $result['A'][0]['gesamtpunkteA']);
	}
}
