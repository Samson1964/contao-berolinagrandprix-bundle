<?php

/**
 * Berolina-GrandPrix für Contao 4.13 und Contao 5
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\DataContainer;
use Contao\DC_Table;

/**
 * Tabelle tl_berolina_grandprix
 */
$GLOBALS['TL_DCA']['tl_berolina_grandprix'] = array
(

	// Konfiguration
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
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

	// Listenansicht
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => DataContainer::MODE_SORTED,
			'fields'                  => array('jahr'),
			'panelLayout'             => 'filter,sort;search,limit',
			'flag'                    => DataContainer::SORT_DESC,
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
				'icon'                => 'edit.svg',
			),
			'editheader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.svg',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.svg'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['toggle'],
				'href'                => 'act=toggle&amp;field=published',
				'icon'                => 'visible.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.svg'
			)
		)
	),

	// Paletten
	'palettes' => array
	(
		'default'                     => '{title_legend},title,jahr;{options_legend},ratingA,ratingB,viewnull,punktgleich;{rating_legend},maxdwz,max,evaluation_order;{players_legend},playerImport,players;{publish_legend},published'
	),

	// Felder
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
			'flag'                    => DataContainer::SORT_DESC,
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
			'default'                 => '10,8,6,5,4,3,2,1',
			'search'                  => true,
			'inputType'               => 'text',
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
			'default'                 => '5,3,2,1',
			'search'                  => true,
			'inputType'               => 'text',
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
			'default'                 => '1799',
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
			'eval'                    => array
			(
				'mandatory'           => true,
				'tl_class'            => 'w50',
				'maxlength'           => 2,
				'rgxp'                => 'natural'
			),
			'sql'                     => "int(1) unsigned NOT NULL default '0'"
		),
		'evaluation_order' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_berolina_grandprix']['evaluation_order'],
			'exclude'                 => true,
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
			'toggle'                  => true,
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
	)
);

/**
 * Stellt Hilfsmethoden für das Data-Container-Array bereit.
 */
class tl_berolina_grandprix extends Backend
{
	/**
	 * Link auf den Import-Assistenten für die Mitgliederliste ausgeben.
	 *
	 * @return string
	 */
	public function getImportlink()
	{
		return '<div class="long widget"><a href="' . $this->addToUrl('key=dwzlist') . '" title="Mitglieder importieren" style="line-height:16px; vertical-align:middle;"><img src="bundles/contaoberolinagrandprix/icons/import.png" alt="" width="16" height="16"> Mitglieder importieren</a></div>';
	}
}
