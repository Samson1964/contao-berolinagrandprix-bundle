<?php

declare(strict_types=1);

/*
 * This file is part of schachbulle/contao-berolinagrandprix-bundle.
 *
 * (c) Frank Hoppe
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoBerolinaGrandPrixBundle\ContentElements;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\Database;
use Contao\StringUtil;
use Contao\System;
use Schachbulle\ContaoBerolinaGrandPrixBundle\Calculator\GrandPrixCalculator;

/**
 * Inhaltselement "Berolina Grand-Prix-Wertung".
 *
 * Das Element lädt lediglich die Stammdaten aus der Datenbank; die eigentliche
 * Berechnung übernimmt der GrandPrixCalculator.
 */
class GrandPrix extends ContentElement
{
	/**
	 * Template.
	 *
	 * @var string
	 */
	protected $strTemplate = 'ce_grandprix';

	/**
	 * {@inheritdoc}
	 */
	public function generate()
	{
		if ($this->isBackendRequest())
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . ($GLOBALS['TL_LANG']['CTE']['berolina-grandprix'][0] ?? 'BEROLINA GRAND-PRIX') . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}

	/**
	 * Die Grand-Prix-Tabellen aufbereiten.
	 */
	protected function compile()
	{
		$intGrandPrix = (int) $this->berolina_grandprix_list; // Gewünschte Meisterschaft
		$intCount = (int) $this->berolina_grandprix_tourcount; // Höchste zu berücksichtigende Turniernummer

		$arrTables = array('A' => array(), 'B' => array());
		$objDatabase = Database::getInstance();

		// Infos zum gewünschten Grand Prix laden
		$objGrandPrix = $objDatabase
			->prepare('SELECT * FROM tl_berolina_grandprix WHERE published=? AND id=?')
			->limit(1)
			->execute('1', $intGrandPrix);

		if ($objGrandPrix->numRows > 0 && $intCount > 0)
		{
			// Turniere in Reihenfolge der Austragung laden
			$objTournaments = $objDatabase
				->prepare('SELECT csv FROM tl_berolina_grandprix_tournaments WHERE published=? AND pid=? ORDER BY date ASC')
				->limit($intCount)
				->execute('1', $intGrandPrix);

			$calculator = new GrandPrixCalculator(
				(int) $objGrandPrix->maxdwz,
				(int) $objGrandPrix->max,
				(bool) $objGrandPrix->punktgleich,
				(string) $objGrandPrix->ratingA,
				(string) $objGrandPrix->ratingB,
				StringUtil::deserialize($objGrandPrix->evaluation_order, true),
				(bool) $objGrandPrix->viewnull
			);

			$arrTables = $calculator->calculate(
				StringUtil::deserialize($objGrandPrix->players, true),
				$objTournaments->fetchEach('csv'),
				$intCount
			);
		}

		$this->Template->tabelleA = $arrTables['A'];
		$this->Template->tabelleB = $arrTables['B'];
		$this->Template->anzahlTurniere = $intCount;
		$this->Template->titel = $objGrandPrix->numRows > 0 ? $objGrandPrix->title : '';
	}

	/**
	 * Prüfen, ob das Element im Backend gerendert wird.
	 *
	 * TL_MODE existiert in Contao 5 nicht mehr, deshalb wird der Scope-Matcher
	 * verwendet - dieser ist in Contao 4.13 und 5 gleichermaßen verfügbar.
	 */
	private function isBackendRequest(): bool
	{
		$container = System::getContainer();

		if (null === $container || !$container->has('request_stack'))
		{
			return false;
		}

		$request = $container->get('request_stack')->getCurrentRequest();

		return null !== $request && $container->get('contao.routing.scope_matcher')->isBackendRequest($request);
	}
}
