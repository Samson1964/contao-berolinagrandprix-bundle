<?php

declare(strict_types=1);

/*
 * This file is part of schachbulle/contao-berolinagrandprix-bundle.
 *
 * (c) Frank Hoppe
 *
 * @license LGPL-3.0-or-later
 */

/*
 * Bootstrap für den Betrieb ohne eigenes vendor-Verzeichnis.
 *
 * Ist ein Composer-Autoloader vorhanden, wird dieser verwendet. Andernfalls
 * werden die beiden Namespaces des Bundles per PSR-4 selbst registriert, damit
 * die Tests auch mit einem extern installierten PHPUnit laufen.
 */

$strComposer = __DIR__ . '/../vendor/autoload.php';

if (file_exists($strComposer))
{
	require $strComposer;

	return;
}

spl_autoload_register(static function (string $strClass): void {
	$arrMap = array
	(
		'Schachbulle\\ContaoBerolinaGrandPrixBundle\\Tests\\' => __DIR__ . '/',
		'Schachbulle\\ContaoBerolinaGrandPrixBundle\\' => __DIR__ . '/../src/',
	);

	foreach ($arrMap as $strPrefix => $strDir)
	{
		if (0 !== strpos($strClass, $strPrefix))
		{
			continue;
		}

		$strFile = $strDir . str_replace('\\', '/', substr($strClass, \strlen($strPrefix))) . '.php';

		if (file_exists($strFile))
		{
			require $strFile;
		}

		return;
	}
});
