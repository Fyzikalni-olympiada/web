<?php
echo '
<h2>Výsledky karlovarských kol '. AKTUALNI_ROCNIK . '.&nbsp;ročníku Fyzikální olympiády</h2>

<p>
Zde budeme během školního roku '. AKTUALNI_ROK . ' uveřejňovat výsledkové listiny všech soutěžních kol Fyzikální olympiády konaných v&nbsp;karlovarském kraji.
</p>

<div id="archiv_vysledky">
';
$soubory = array();
if (is_dir(ROOT_DIR.DIR_VYSLEDKY)) {
	$adresar = opendir(ROOT_DIR.DIR_VYSLEDKY);	// otevre adresar
	while ($zaznam = readdir($adresar)) {	// nacte nazev souboru
		if (!is_dir(ROOT_DIR.DIR_VYSLEDKY.$zaznam) && $zaznam[0] != '.') {
			// zkontroluje zda neni nazev adresar nebo skryty soubor
			$soubory[] = $zaznam; // ulozi do pole soubor
		}
	}
	closedir($adresar);	// uzavre adresar
}	

$odkaz_text_array = array();
foreach ($soubory as $soubor_name) {
	list($rocnik,$kategorie_str,$koloapripona) = explode('_', $soubor_name);
	list($kolo,$pripona) = explode('.',$koloapripona);
	$kategorie_array = preg_split('//', $kategorie_str, -1, PREG_SPLIT_NO_EMPTY);
	foreach ($kategorie_array as $kategorie) {
		switch ($kolo) {
			case 'dom':
				$kolo_text = '';
				break;
			case 'okr':
				$kolo_text = '';
				break;
			case 'reg':
				$kolo_text = '';
				break;
			case 'postup':
				$kolo_text = 'postupující do krajského kola ';
				break;
			default:
				$kolo_text = 'výsledky ';
				break;
		}
		if ($rocnik == AKTUALNI_ROCNIK)
		$odkaz_text_array[$rocnik][$kolo][$kategorie] = $kolo_text.'kategorie '.$kategorie;
	}
}
krsort($odkaz_text_array);

foreach ($odkaz_text_array as $rocnik => $odkaz_text_sub_array) {
	echo '
	<dl>';
	krsort($odkaz_text_sub_array);
	foreach ($odkaz_text_sub_array as $kolo => $odkaz_text_subsub_array) {
		switch ($kolo) {
			case 'dom':
				$kolo_text = 'Domácí kolo';
				break;
			case 'okr':
				$kolo_text = 'Obvodní kolo';
				break;
			case 'reg':
				$kolo_text = 'Krajské kolo';
				break;
			default:
				$kolo_text = 'Další výsledkové listiny';
				break;
		}
		echo '
		<dt>' . $kolo_text . '
		<dd>';
		ksort($odkaz_text_subsub_array);
		$prvni = 1;
		foreach ($odkaz_text_subsub_array as $kategorie => $odkaz_text) {
			if (!$prvni) {
				$oddelovac = '&nbsp;| ';
			} else {
				$oddelovac = '';
				$prvni = 0;
			}
			echo $oddelovac.'<a href="' . odkaz('kvary/content/listina.php') . '&rocnik='.$rocnik.'&kolo='.$kolo.'&kategorie='.$kategorie . '" title="' .$kolo_text .' '. $odkaz_text . '">'.$odkaz_text.'</a>';
		}
		echo '
		</dd>';
	}
	echo '
	</dl>';
}
echo '
<p>
Starší výsledkové listiny najdete v&nbsp;<a href="' . odkaz('kvary/content/archiv.php') . '" title="Archiv výsledků">archivu</a>.
</p>
</div> <!-- archiv_vysledky -->';

?>