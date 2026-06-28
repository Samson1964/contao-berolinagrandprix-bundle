<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package   fen
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2013
 */

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['berolina-grandprix'] = '{type_legend},type,headline;{berolina_grandprix_legend},berolina_grandprix_list,berolina_grandprix_tourcount;{protected_legend:hide},protected;{expert_legend:hide},guest,cssID,space;{invisible_legend:hide},invisible,start,stop';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['berolina_grandprix_list'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['berolina_grandprix_list'],
	'exclude'                 => true,
	'options_callback'        => array('tl_content_berolina_grandprixlist', 'getGrandPrixLists'),
	'inputType'               => 'select',
	'eval'                    => array
	(
		'mandatory'           => true, 
		'multiple'            => false, 
		'chosen'              => true,
		'submitOnChange'      => true,
		'tl_class'            => 'w50 wizard'
	),
	'wizard'                  => array
	(
		array('tl_content_berolina_grandprixlist', 'editListe')
	),
	'sql'                     => "int(10) unsigned NOT NULL default '0'" 
);

$GLOBALS['TL_DCA']['tl_content']['fields']['berolina_grandprix_tourcount'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['berolina_grandprix_tourcount'],
	'inputType'               => 'text',
	'eval'                    => array
	(
		'tl_class'            => 'w50', 
		'maxlength'           => 2,
	),
	'sql'                     => "int(2) unsigned NOT NULL default '0'"
);

/*****************************************
 * Klasse tl_content_grandprixlist
 *****************************************/
 
class tl_content_berolina_grandprixlist extends \Contao\Backend
{

	/**
	 * Funktion editListe
	 * @param \Contao\DataContainer
	 * @return string
	 */
	public function editListe(\Contao\DataContainer $dc)
	{
		if($dc->value < 1)
		{
			return '';
		}

		$title = sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'], $dc->value);

		// Backend-URL und Request-Token über die Services erzeugen
		// (contao/main.php und die Konstante REQUEST_TOKEN existieren in Contao 5 nicht mehr)
		$strToken = \Contao\System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
		$strUrl = \Contao\System::getContainer()->get('router')->generate('contao_backend', array
		(
			'do'    => 'berolina-grandprix',
			'act'   => 'edit',
			'id'    => $dc->value,
			'popup' => 1,
			'rt'    => $strToken,
		));

		return ' <a href="' . \Contao\StringUtil::specialchars($strUrl) . '" title="' . \Contao\StringUtil::specialchars($title) . '" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':765,\'title\':\'' . \Contao\StringUtil::specialchars(str_replace("'", "\\'", $title)) . '\',\'url\':this.href});return false">' . \Contao\Image::getHtml('alias.svg', $title) . '</a>';
	}

	public function getGrandPrixLists(\Contao\DataContainer $dc)
	{
		$array = array();
		$objListe = \Contao\Database::getInstance()->prepare("SELECT * FROM tl_berolina_grandprix ORDER BY jahr DESC")->execute();
		while($objListe->next())
		{
			$array[$objListe->id] = $objListe->jahr . ' - ' . $objListe->title;
		}
		return $array;

	}

	public function getTemplates($dc)
	{
		return $this->getTemplateGroup('mod_berolinagrandprixlists_', $dc->activeRecord->id);
	} 

}
