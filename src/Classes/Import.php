<?php

declare(strict_types=1);

/*
 * This file is part of schachbulle/contao-berolinagrandprix-bundle.
 *
 * (c) Frank Hoppe
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoBerolinaGrandPrixBundle\Classes;

use Contao\Backend;
use Contao\Config;
use Contao\DataContainer;
use Contao\Database;
use Contao\Environment;
use Contao\File;
use Contao\FileUpload;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;

/**
 * Import einer Mitgliederliste (CSV) in das Feld tl_berolina_grandprix.players.
 */
class Import extends Backend
{
	/**
	 * Formular zur Auswahl einer CSV-Datei ausgeben und den Import durchführen.
	 *
	 * @return string
	 */
	public function ImportListe(DataContainer $dc)
	{
		if ('dwzlist' !== Input::get('key'))
		{
			return '';
		}

		// Datei-Upload-Widget instanzieren (Contao 5: $this->User->uploader entfällt)
		$objUploader = new FileUpload();

		// CSV importieren
		if ('tl_berolinagrandprix_import' === Input::post('FORM_SUBMIT'))
		{
			$arrUploaded = $objUploader->uploadTo('system/tmp');

			if (empty($arrUploaded))
			{
				Message::addError($GLOBALS['TL_LANG']['ERR']['all_fields']);
				$this->reload();
			}

			$strSeparator = $this->getSeparator((string) Input::post('separator'));
			$arrImport = array();

			foreach ($arrUploaded as $strCsvFile)
			{
				$objFile = new File($strCsvFile);

				if ('csv' !== $objFile->extension)
				{
					Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension));
					continue;
				}

				// Der Escape-Parameter wird bewusst leer übergeben, weil sein
				// Standardwert "\" ab PHP 8.4 als veraltet gilt (RFC 4180).
				while (false !== ($arrRow = fgetcsv($objFile->handle, 0, $strSeparator, '"', '')))
				{
					// Leere Zeilen (fgetcsv liefert dafür array(null)) überspringen
					if (null === ($arrRow[0] ?? null) && 1 === \count($arrRow))
					{
						continue;
					}

					$strName = trim((string) ($arrRow[0] ?? ''));

					if ('' === $strName)
					{
						continue;
					}

					$arrImport[] = array
					(
						'playername' => $strName,
						'playerdwz'  => trim((string) ($arrRow[1] ?? '')),
						'excluded'   => '',
					);
				}

				$objFile->close();
			}

			if (empty($arrImport))
			{
				Message::addError('Die CSV-Datei enthält keine auswertbaren Zeilen.');
				$this->reload();
			}

			$intId = (int) Input::get('id');

			$objVersions = new Versions($dc->table, $intId);
			$objVersions->initialize();

			Database::getInstance()
				->prepare('UPDATE ' . $dc->table . ' SET tstamp=?, players=? WHERE id=?')
				->execute(time(), serialize($arrImport), $intId);

			$objVersions->create();

			Message::addConfirmation(sprintf('%s Mitglieder wurden importiert.', \count($arrImport)));

			$this->redirect(str_replace('&key=dwzlist', '', Environment::get('request')));
		}

		// Request-Token (die Konstante REQUEST_TOKEN existiert in Contao 5 nicht mehr)
		$strToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();

		$strBackUrl = StringUtil::ampersand(str_replace('&key=dwzlist', '', Environment::get('request')));
		$strHelp = $GLOBALS['TL_LANG']['MSC']['separator'][1] ?? '';
		$strSourceHelp = $GLOBALS['TL_LANG']['MSC']['source'][1] ?? '';

		return '
<div id="tl_buttons">
<a href="' . $strBackUrl . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>
' . Message::generate() . '
<form action="' . StringUtil::ampersand(Environment::get('request')) . '" id="tl_table_import" class="tl_form" method="post" enctype="multipart/form-data">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_berolinagrandprix_import">
<input type="hidden" name="REQUEST_TOKEN" value="' . $strToken . '">
<input type="hidden" name="MAX_FILE_SIZE" value="' . Config::get('maxFileSize') . '">

<div class="tl_tbox widget">
  <h3><label for="separator">' . $GLOBALS['TL_LANG']['MSC']['separator'][0] . '</label></h3>
  <select name="separator" id="separator" class="tl_select" onfocus="Backend.getScrollOffset()">
    <option value="comma">' . $GLOBALS['TL_LANG']['MSC']['comma'] . '</option>
    <option value="semicolon">' . $GLOBALS['TL_LANG']['MSC']['semicolon'] . '</option>
    <option value="tabulator">' . $GLOBALS['TL_LANG']['MSC']['tabulator'] . '</option>
  </select>' . ('' !== $strHelp ? '
  <p class="tl_help tl_tip">' . $strHelp . '</p>' : '') . '
  <h3>' . $GLOBALS['TL_LANG']['MSC']['source'][0] . '</h3>' . $objUploader->generateMarkup() . ('' !== $strSourceHelp ? '
  <p class="tl_help tl_tip">' . $strSourceHelp . '</p>' : '') . '
  <h3>Hinweise zur CSV-Datei</h3>
  <p class="tl_help tl_tip">Je Zeile ein Mitglied im Format: Name,Vorname[Trennzeichen]DWZ</p>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_import'][0]) . '">
</div>

</div>
</form>';
	}

	/**
	 * Das gewählte Spaltentrennzeichen ermitteln.
	 */
	private function getSeparator(string $strChoice): string
	{
		switch ($strChoice)
		{
			case 'semicolon':
				return ';';

			case 'tabulator':
				return "\t";

			default:
				return ',';
		}
	}
}
