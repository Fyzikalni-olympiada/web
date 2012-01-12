<?php
if (isset($_GET['rocnik']) && isset($_GET['kolo']) && isset($_GET['kategorie'])) {
	$rocnik = $_GET['rocnik'];
	$kolo = $_GET['kolo'];
	$kategorie = $_GET['kategorie'];
} else {
	echo '<p>Chybý vstup pro výsledkovou listinu.</p>';
	exit();
}
$test_string = '/'.$rocnik.'_[A-G]*'.$kategorie.'[A-G]*_'.$kolo.'.html/i';

$soubory = array();
$soubor = null;
if (is_dir(ROOT_DIR.DIR_VYSLEDKY)) {
	$adresar = opendir(ROOT_DIR.DIR_VYSLEDKY);	// otevre adresar
	while ($zaznam = readdir($adresar)) {	// nacte nazev souboru
		if (!is_dir(ROOT_DIR.DIR_VYSLEDKY.$zaznam) && $zaznam[0] != '.') {
			// zkontroluje zda neni nazev adresar nebo skryty soubor
			$soubory[] = $zaznam; // ulozi do pole soubor
			if (preg_match($test_string, $zaznam)) {
				$soubor = $zaznam;
				break;
			}
		}
	}
	closedir($adresar);	// uzavre adresar
}	

if (!empty($soubor)) {
	include(ROOT_DIR.DIR_VYSLEDKY.$soubor);
} else {
	echo '<p>Výsledková listina není k dispozici.</p>';
}

?>