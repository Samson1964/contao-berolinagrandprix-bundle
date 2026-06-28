<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoBerolinaGrandPrixBundle\Classes;


/**
 * Provide methods to handle table fields.
 *
 * @property integer $rows
 * @property integer $cols
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class Import extends \Contao\Backend
{

	/**
	 * Return a form to choose a CSV file and import it
	 *
	 * @param \Contao\DataContainer $dc
	 *
	 * @return string
	 */
	public function ImportListe(\Contao\DataContainer $dc)
	{
		if (\Contao\Input::get('key') != 'dwzlist')
		{
			return '';
		}

		// Datei-Upload-Widget instanzieren (Contao 5: $this->User->uploader entfällt)
		$objUploader = new \Contao\FileUpload();

		// CSV importieren
		if (\Contao\Input::post('FORM_SUBMIT') == 'tl_berolinagrandprix_import')
		{
			$arrUploaded = $objUploader->uploadTo('system/tmp');

			if (empty($arrUploaded))
			{
				\Contao\Message::addError($GLOBALS['TL_LANG']['ERR']['all_fields']);
				$this->reload();
			}

			$this->import('Database');
			$arrTable = array();

			foreach ($arrUploaded as $strCsvFile)
			{
				$objFile = new \Contao\File($strCsvFile);

				if($objFile->extension != 'csv')
				{
					\Contao\Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension));
					continue;
				}

				// Get separator
				switch (\Contao\Input::post('separator'))
				{
					case 'semicolon':
						$strSeparator = ';';
						break;

					case 'tabulator':
						$strSeparator = "\t";
						break;

					default:
						$strSeparator = ',';
						break;
				}

				$resFile = $objFile->handle;

				while(($arrRow = @fgetcsv($resFile, null, $strSeparator)) !== false)
				{
					$arrTable[] = $arrRow;
				}
			}

			// Array mit den CSV-Daten umwandeln
			$arrImport = array();
			foreach($arrTable as $item)
			{
				$arrImport[] = array
				(
					'playername' => $item[0],
					'playerdwz'  => $item[1],
					'excluded'   => ''
				);
			}

			$objVersions = new \Contao\Versions($dc->table, \Contao\Input::get('id'));
			$objVersions->create();

			$this->Database->prepare("UPDATE " . $dc->table . " SET players=? WHERE id=?")
			               ->execute(serialize($arrImport), \Contao\Input::get('id'));

			\Contao\System::setCookie('BE_PAGE_OFFSET', 0, 0);
			$this->redirect(str_replace('&key=dwzlist', '', \Contao\Environment::get('request')));
		}

		// Request-Token (REQUEST_TOKEN-Konstante existiert in Contao 5 nicht mehr)
		$strToken = \Contao\System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();

		// Return form
		return '
<div id="tl_buttons">
<a href="'.\Contao\StringUtil::ampersand(str_replace('&key=dwzlist', '', \Contao\Environment::get('request'))).'" class="header_back" title="'.\Contao\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>
'.\Contao\Message::generate().'
<form action="'.\Contao\StringUtil::ampersand(\Contao\Environment::get('request'), true).'" id="tl_table_import" class="tl_form" method="post" enctype="multipart/form-data">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_berolinagrandprix_import">
<input type="hidden" name="REQUEST_TOKEN" value="'.$strToken.'">

<div class="tl_tbox widget">
  <h3><label for="separator">'.$GLOBALS['TL_LANG']['MSC']['separator'][0].'</label></h3>
  <select name="separator" id="separator" class="tl_select" onfocus="Backend.getScrollOffset()">
    <option value="comma">'.$GLOBALS['TL_LANG']['MSC']['comma'].'</option>
    <option value="semicolon">'.$GLOBALS['TL_LANG']['MSC']['semicolon'].'</option>
    <option value="tabulator">'.$GLOBALS['TL_LANG']['MSC']['tabulator'].'</option>
  </select>'.(($GLOBALS['TL_LANG']['MSC']['separator'][1] != '') ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['separator'][1].'</p>' : '').'
  <h3>'.$GLOBALS['TL_LANG']['MSC']['source'][0].'</h3>'.$objUploader->generateMarkup().(isset($GLOBALS['TL_LANG']['MSC']['source'][1]) ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['source'][1].'</p>' : '').'
  <h3>Hinweise zur CSV-Datei</h3>
  <p class="tl_help tl_tip">Je Zeile ein Mitglied im Format: Name,Vorname[Trennzeichen]DWZ</p>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.\Contao\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_import'][0]).'">
</div>

</div>
</form>';
	}
}
