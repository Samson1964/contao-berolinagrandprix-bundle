<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   Elo
 * @author    Frank Hoppe
 * @license   GNU/LPGL
 * @copyright Frank Hoppe 2016
 */


/**
 * Table tl_berolina_grandprix
 */
$GLOBALS['TL_DCA']['tl_berolina_grandprix'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_berolina_grandprix_tournaments'),
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id'                  => 'primary',
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('jahr'),
			'panelLayout'             => 'filter,sort;search,limit',
			'flag'                    => 12,
			'disableGrouping'         => true,
		),
		'label' => array
		(
			'fields'                  => array('id', 'jahr', 'title'),
			'showColumns'             => true,
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['edit'],
				'href'                => 'table=tl_berolina_grandprix_tournaments',
				'icon'                => 'edit.gif',
			),
			'editheader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.gif',
			),  
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'toggle' => array
			(
				'label'                => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['toggle'],
				'attributes'           => 'onclick="Backend.getScrollOffset()"',
				'haste_ajax_operation' => array
				(
					'field'            => 'published',
					'options'          => array
					(
						array('value' => '', 'icon' => 'invisible.svg'),
						array('value' => '1', 'icon' => 'visible.svg'),
					),
				),
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Select
	'select' => array
	(
		'buttons_callback' => array()
	),

	// Edit
	'edit' => array
	(
		'buttons_callback' => array()
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array(''),
		'default'                     => '{title_legend},title,jahr;{options_legend},ratingA,ratingB,viewnull,punktgleich;{rating_legend},maxdwz,max,evaluation_order;{players_legend},playerImport,players;{publish_legend},published'
	),

	// Subpalettes
	'subpalettes' => array
	(
		''                            => ''
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['id'],
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['title'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'filter'                  => true,
			'search'                  => true,
			'eval'                    => array
			(
				'mandatory'           => true, 
				'tl_class'            => 'w50',
				'maxlength'           => 255
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'jahr' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['jahr'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'inputType'               => 'text',
			'flag'                    => 12,
			'eval'                    => array
			(
				'mandatory'           => true, 
				'tl_class'            => 'w50',
				'maxlength'           => 4,
				'rgxp'                => 'digit'
			),
			'sql'                     => "varchar(4) NOT NULL default ''"
		),
		'ratingA' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['ratingA'],
			'exclude'                 => true,
			'filter'                  => true,
			'default'                 => '10,8,6,5,4,3,2,1',
			'search'                  => true,
			'inputType'               => 'text',
			'flag'                    => 12,
			'eval'                    => array
			(
				'mandatory'           => true, 
				'tl_class'            => 'long',
				'maxlength'           => 255
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'ratingB' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['ratingB'],
			'exclude'                 => true,
			'filter'                  => true,
			'default'                 => '5,3,2,1',
			'search'                  => true,
			'inputType'               => 'text',
			'flag'                    => 12,
			'eval'                    => array
			(
				'mandatory'           => true, 
				'tl_class'            => 'long',
				'maxlength'           => 255
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'viewnull' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['viewnull'],
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'default'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		), 
		'punktgleich' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['punktgleich'],
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'default'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		), 
		'maxdwz' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['maxdwz'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'inputType'               => 'text',
			'default'                 => 1799,
			'flag'                    => 12,
			'eval'                    => array
			(
				'mandatory'           => true, 
				'tl_class'            => 'w50',
				'maxlength'           => 4,
				'rgxp'                => 'digit'
			),
			'sql'                     => "varchar(4) NOT NULL default ''"
		),
		'max' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['max'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'default'                 => 5,
			'filter'                  => true,
			'search'                  => true,
			'eval'                    => array
			(
				'mandatory'           => true, 
				'tl_class'            => 'w50',
				'maxlength'           => 1
			),
			'sql'                     => "int(1) unsigned NOT NULL default '0'"
		),
		'evaluation_order' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['evaluation_order'],
			'exclude'                 => true,
			'default'                 => 0,
			'inputType'               => 'checkboxWizard',
			'options'                 => array('1', '2', '3', '4'),
			'eval'                    => array
			(
				'multiple'            => true,
				'tl_class'            => 'clr long'
			),
			'reference'               => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['evaluation_order_options'],
			'sql'                     => "blob NULL"
		),
		'playerImport' => array
		(
			'exclude'                 => true,
			'input_field_callback'    => array('tl_berolina_grandprix', 'getImportlink')
		), 
		'players' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['players'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'multiColumnWizard',
			'eval'                    => array
			(
				'columnFields'        => array
				(
					'playername' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['playername'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style'             => 'width:400px', 
						)
					),
					'playerdwz' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['playerdwz'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style'             => 'width:50px', 
							'maxlength'         => 4,
							'rgxp'              => 'digit'
						)
					),
					'excluded' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['excluded'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style'             => 'width:100px', 
						)
					),
				)
			),
			'sql'                     => "blob NULL"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['published'],
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default ''"
		), 
	)
);


/**
 * Class tl_berolina_grandprix
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    News
 */
class tl_berolina_grandprix extends Backend
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
	 * Add a link to the table items import wizard
	 *
	 * @return string
	 */
	public function getImportlink()
	{
		return '<div class="long widget"><a href="' . $this->addToUrl('key=dwzlist') . '" title="Mitglieder importieren" style="line-height:16px; vertical-align:middle;"><img src="bundles/contaoberolinagrandprix/icons/import.png"> Mitglieder importieren</a></div>';
	}
}
