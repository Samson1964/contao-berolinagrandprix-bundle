<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

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
$GLOBALS['TL_DCA']['tl_content']['palettes']['berolina-grandprix'] = '{type_legend},type,headline;{berolina_legend},berolina_grandprix_list,berolina_grandprix_tourcount;{protected_legend:hide},protected;{expert_legend:hide},guest,cssID,space;{invisible_legend:hide},invisible,start,stop';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['berolina_grandprix_list'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['berolina_grandprix_list'],
	'exclude'              => true,
	'options_callback'     => array('tl_content_berolina_grandprixlist', 'getGrandPrixLists'),
	'inputType'            => 'select',
	'eval'                 => array
	(
		'mandatory'      => true, 
		'multiple'       => false, 
		'chosen'         => true,
		'submitOnChange' => true,
		'tl_class'       => 'long wizard'
	),
	'wizard'               => array
	(
		array('tl_content_berolina_grandprixlist', 'editListe')
	),
	'sql'                  => "int(10) unsigned NOT NULL default '0'" 
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
 
class tl_content_berolina_grandprixlist extends \Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	/**
	 * Funktion editListe
	 * @param \DataContainer
	 * @return string
	 */
	public function editListe(DataContainer $dc)
	{
		return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=berolina-grandprix&amp;id=' . $dc->value . '&amp;popup=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $dc->value) . '" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':765,\'title\':\'' . specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'][1], $dc->value))) . '\',\'url\':this.href});return false">' . Image::getHtml('alias.gif', $GLOBALS['TL_LANG']['tl_content']['editalias'][0], 'style="vertical-align:top"') . '</a>';
	} 
	
	public function getGrandPrixLists(DataContainer $dc)
	{
		$array = array();
		$objListe = $this->Database->prepare("SELECT * FROM tl_berolina_grandprix ORDER BY jahr DESC")->execute();
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

?>
