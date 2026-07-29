<?php

/**
 * Berolina-GrandPrix für Contao 4.13 und Contao 5
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

use Schachbulle\ContaoBerolinaGrandPrixBundle\Classes\Import;
use Schachbulle\ContaoBerolinaGrandPrixBundle\ContentElements\GrandPrix;

/**
 * Backend-Module
 */
$GLOBALS['BE_MOD']['content']['berolina-grandprix'] = array
(
	'tables'  => array('tl_berolina_grandprix', 'tl_berolina_grandprix_tournaments'),
	'icon'    => 'bundles/contaoberolinagrandprix/icons/icon.png',
	'dwzlist' => array(Import::class, 'ImportListe'),
);

/**
 * Inhaltselemente
 */
$GLOBALS['TL_CTE']['schach']['berolina-grandprix'] = GrandPrix::class;
