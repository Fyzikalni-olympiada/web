<?php
//    Ochrana proti neoprávněnému přístupu ke skriptům
if (!defined("VALID_ACCESS")) {
    die("Neoprávněný přístup!");
}



function datetime()
{
    $day = date("j");
    $month = date("n");
    $year = date("Y");
    $monthz = array(1=>'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince');
    return $day . '. ' . $monthz[$month] . ' ' . $year;
}

/* ----- Odkazy ----- */



/** URL stránky podle pathname z data/files.yaml */
function url_for_pathname($pathname)
{
    return $pathname === '/' ? ROOT_WWW : ROOT_WWW . ltrim($pathname, '/');
}



/**
 * Odkaz na obsahový soubor (např. 'terminy.html' nebo 'html/terminy.html').
 * Parametr $kdo je ponechán kvůli BC, web je jen studentský.
 */
function odkaz($souborName, $kdo = null, $amp_entity = 1)
{
    $map = data_pathname_by_file();
    if (isset($map[$souborName])) {
        return url_for_pathname($map[$souborName]);
    }
    if (isset($map['html/' . $souborName])) {
        return url_for_pathname($map['html/' . $souborName]);
    }
    return ROOT_WWW;
}

/* ----- Menu ----- */



/** Cíl položky menu; položka bez vlastního cíle odkazuje na svého prvního potomka */
function menu_target($node)
{
    if (isset($node['file'])) {
        return url_for_pathname($node['file']);
    }
    if (isset($node['href'])) {
        return $node['href'];
    }
    if (!empty($node['children'])) {
        return menu_target($node['children'][0]);
    }
    return ROOT_WWW;
}



/** Je aktuální stránka v podstromu položky? */
function menu_contains($node, $pathname)
{
    if (isset($node['file']) && $node['file'] === $pathname) {
        return true;
    }
    foreach (isset($node['children']) ? $node['children'] : [] as $child) {
        if (menu_contains($child, $pathname)) {
            return true;
        }
    }
    return false;
}



function menu()
{
    $current = isset($GLOBALS['pathname']) ? $GLOBALS['pathname'] : null;
    $strHTML = '
                <ul class="tabbed">';

    $i = 1;
    foreach (data_menu() as $node) {
        $url = menu_target($node);
        $title = isset($node['title']) ? $node['title'] : $node['name'];
        $selected = menu_contains($node, $current)
            || (isset($node['file']) && $node['file'] === 'diskuse' && strpos((string) $current, 'diskuse') === 0);

        if (isset($node['file']) && $node['file'] === 'diskuse') { //diskuse: studenti/ucitele
            $strHTML .= '
				<li class="dropdown' . ($selected ? ' current-tab' : '') . ($i++ > 5 ? ' smaller' : '') . '">
				<a href="' . $url . '" title="' . $title . '" id="diskuse-drop" role="button" class="dropdown-toggle" data-toggle="dropdown">' . $node['name'] . '<b class="caret"></b></a>
				<ul class="dropdown-menu" role="menu" aria-labelledby="diskuse-drop">
					<li><a href="' . ROOT_WWW . 'diskuse">Studenti</a></li>
					<li><a href="' . ROOT_WWW . 'diskuse-ucitele">Učitelé</a></li>
				</ul>';
        } else {
            $dropdown = ($submenu = submenu($node, true));
            $strHTML .= '
			<li class="' . ($selected ? 'current-tab' : ($dropdown ? 'dropdown' : '')) . ($i++ > 5 ? ' smaller' : '') . '">
			<a href="' . $url . '" title="' . $title . '"' . ($dropdown ? ' role="button" class="dropdown-toggle" data-toggle="dropdown"' : '') . '>' . $node['name'] . ($dropdown ? '<b class="caret"></b>' : '') . '</a>';
            if ($dropdown) {
                $strHTML .= $submenu;
            }
        }
        $strHTML .= ' ' . MENU_ODDELOVAC . '</li>';
    }
    $strHTML .= '<li><a title="Odevzdávací systém Fyzikální olympiády"
		target="_blank" href="https://osmo.fyzikalniolympiada.cz/">Osmo</a></li>';
    $strHTML .= '
                </ul>';

    return $strHTML;
}



function submenu($node, $dropdown = false)
{
    if (empty($node['children'])) {
        return '';
    }
    $current = isset($GLOBALS['pathname']) ? $GLOBALS['pathname'] : null;
    $strHTML = '
                <ul ' . ($dropdown ? 'class="dropdown-menu" role="menu"' : 'class="tabbed"') . '>';

    foreach ($node['children'] as $child) {
        $url = menu_target($child);
        $title = isset($child['title']) ? $child['title'] : $child['name'];
        $selected = (isset($child['file']) && $child['file'] === $current) ? ' class="current-tab"' : '';

        $strHTML .= '
				<li' . $selected . '>' . SUBMENU_ODRAZKA . '<a href="' . $url . '" title="' . $title . '"' . $selected . '>' . $child['name'] . '</a></li>';
    }
    $strHTML .= '
                </ul>';
    return $strHTML;
}

/* ----- Termíny ----- */



/** Termíny dané kategorie (výchozí je aktuální ročník) */
function terms_kategorie($kategorie, $rocnik = null)
{
    $rocnik = $rocnik === null ? AKTUALNI_ROCNIK : $rocnik;
    $terms = data_terms($rocnik);
    return isset($terms[$kategorie]) ? $terms[$kategorie] : array();
}



/** Termíny podle podřetězce názvu, volitelně kategorie; seřazeno podle kategorie */
function terms_hledej($nazev, $kategorie = null, $rocnik = null)
{
    $rocnik = $rocnik === null ? AKTUALNI_ROCNIK : $rocnik;
    $vysledek = array();
    foreach (data_terms($rocnik) as $kat => $list) {
        if ($kategorie !== null && $kat != $kategorie) {
            continue;
        }
        foreach ($list as $t) {
            if (mb_stripos($t['nazev'], $nazev) !== false) {
                $t['kategorie'] = $kat;
                $vysledek[] = $t;
            }
        }
    }
    return $vysledek;
}

/* ----- Novinky ----- */



/** Novinky zobrazované na hlavní stránce (homepage: true) */
function news_homepage()
{
    return array_values(array_filter(data_news(), function ($item) {
        return !empty($item['homepage']);
    }));
}



/* ----- Fotogalerie (zmrazený archiv celostátních kol) ----- */



/**
 * Vypíše fotogalerii: fotky v $dir/photos, náhledy v $dir/thumbnails,
 * volitelné popisky v $dir/popisky.csv (soubor;popisek).
 * Datum v titulku se čte z prefixu názvu YYYYMMDD; pro názvy DD_HHMMSS
 * předej mapu $dny (kód dne => datum), doplní se čas.
 */
function fotogalerie($dir, $dny = null)
{
    $popisky = array();
    $csv = ROOT_DIR . $dir . '/popisky.csv';
    if (is_file($csv)) {
        $handle = fopen($csv, 'r');
        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            $popisky[$data[0]] = $data[1];
        }
        fclose($handle);
    }

    echo '
<script src="js/jquery.lightbox-0.5.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function() {
        $(\'.photoContainer a\').lightBox({
            fixedNavigation: true,
            imageLoading: \'pic/lightboxes/lightbox-ico-loading.gif\',
            imageBtnPrev: \'pic/lightboxes/lightbox-btn-prev.gif\',
            imageBtnNext: \'pic/lightboxes/lightbox-btn-next.gif\',
            imageBtnClose: \'pic/lightboxes/lightbox-btn-close.gif\',
            imageBlank: \'pic/lightboxes/lightbox-blank.gif\',
            containerBorderSize: 10,
            containerResizeSpeed: 400,
            txtImage: \'Obrázek\',
            txtOf: \'z\'
        });
    });
</script>

<div id="photoGallery">';

    $nahledy = array_flip(scandir(ROOT_DIR . $dir . '/thumbnails'));
    foreach (glob(ROOT_DIR . $dir . '/photos/*') as $foto) {
        $soubor = basename($foto);
        if (!isset($nahledy[$soubor])) {
            continue;
        }
        if ($dny !== null) { // názvy DD_HHMMSS…
            $title = isset($dny[substr($soubor, 0, 2)]) ? $dny[substr($soubor, 0, 2)] : '';
            $title .= ', ' . substr($soubor, 3, 2) . ':' . substr($soubor, 5, 2) . ':' . substr($soubor, 7, 2);
        } else { // názvy YYYYMMDD…
            $title = ltrim(substr($soubor, 6, 2), '0') . '. ' . ltrim(substr($soubor, 4, 2), '0') . '. ' . substr($soubor, 0, 4);
        }
        if (!empty($popisky[$soubor])) {
            $title .= ' &ndash; ' . $popisky[$soubor];
        }
        echo '
	<div class="photoContainer">
		<a href="' . ROOT_WWW . $dir . '/photos/' . $soubor . '" title="' . $title . '">
		<img src="' . ROOT_WWW . $dir . '/thumbnails/' . $soubor . '" alt="' . $title . '" loading="lazy" />
		</a>
	</div>';
    }

    echo '
<div style="clear: left"></div>
</div>';
}



function nadpis()
{
	if ($GLOBALS['nadpis'] === NULL) {
		return '404';
	}
    return $GLOBALS['nadpis'];
}



/**
 * Parsovani pozadavku: podle PATH_INFO nastavi $napln, $nadpis, $pathname
 */
function parsuj()
{
    setlocale(LC_CTYPE, 'cs_CZ.utf8');

	if (isset($_GET['file'])) {
		// prastaré odkazy ?file=N už nepřekládáme
		header('Location: /', TRUE, 301);
		exit();
	}

	$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
	$pathname = ltrim($path_info, '/');
	$pathname = iconv('UTF-8', 'ASCII//TRANSLIT', $pathname);
	if ($pathname === '' || $pathname === false) {
		$pathname = '/';
	}

	// 301
	$redirects = array(
		'zajimavosti/korespondencni-seminare' => '/korespondencni-seminare',
		'zajimavosti/jine-olympiady' => '/jine-olympiady',
		'zajimavosti/odkazy' => '/odkazy',
		'archiv/studijni-texty' => '/studijni-texty',
		'novinky' => '/',
	);
	if (isset($redirects[$pathname])) {
		header('Location: ' . $redirects[$pathname], TRUE, 301);
		exit();
	}

	$GLOBALS['pathname'] = $pathname;
	$GLOBALS['news_archiv'] = false;
	$GLOBALS['novinka_id'] = null;
	$GLOBALS['forum_who'] = null;
	$GLOBALS['forum_page'] = 1;
	$GLOBALS['forum_thread'] = null;

	/* Zvláštní stránky mimo data/files.yaml */
	if ($pathname === 'archiv-novinek') {
		$GLOBALS['nadpis'] = 'Archiv novinek';
		$GLOBALS['napln'] = FILE_NEWS;
		$GLOBALS['news_archiv'] = true;
		return;
	}
	if (preg_match('~^novinka/(\d+)$~', $pathname, $m)) {
		$novinka = data_news_by_id((int) $m[1]);
		if ($novinka !== null) {
			$GLOBALS['nadpis'] = 'Aktuality';
			$GLOBALS['napln'] = 'content/novinka.php';
			$GLOBALS['novinka_id'] = (int) $m[1];
			return;
		}
	}
	if (preg_match('~^diskuse(-ucitele)?(?:/(\d+))?$~', $pathname, $m)) {
		$GLOBALS['nadpis'] = 'Diskusní fórum';
		$GLOBALS['napln'] = FILE_FORUM;
		$GLOBALS['forum_who'] = empty($m[1]) ? 'student' : 'ucitel';
		$GLOBALS['forum_page'] = empty($m[2]) ? 1 : (int) $m[2];
		return;
	}
	if (preg_match('~^diskuse/vlakno/(\d+)$~', $pathname, $m)) {
		$GLOBALS['nadpis'] = 'Diskusní fórum';
		$GLOBALS['napln'] = FILE_FORUM;
		$GLOBALS['forum_thread'] = (int) $m[1];
		return;
	}

	$routes = data_routes();
	if (isset($routes[$pathname]) && file_exists(ROOT_DIR . $routes[$pathname]['file'])) {
		$GLOBALS['nadpis'] = $routes[$pathname]['title'];
		$GLOBALS['napln'] = $routes[$pathname]['file'];
	} else {
		header("HTTP/1.1 404 Not Found");
		$GLOBALS['nadpis'] = NULL;
		$GLOBALS['napln'] = 404;
	}
}


function lng($cz = 'cz', $en = 'en')
{
    return $cz;
}



function vlnka($data)
{
    $data = str_replace(' - ', "&nbsp;&ndash; ", $data);
    $data = str_replace(' k ', " k&nbsp;", $data);
    $data = str_replace(' s ', " s&nbsp;", $data);
    $data = str_replace(' v ', " v&nbsp;", $data);
    $data = str_replace(' u ', " u&nbsp;", $data);
    $data = str_replace(' o ', " o&nbsp;", $data);
    $data = str_replace(' a ', " a&nbsp;", $data);
    $data = str_replace(' i ', " i&nbsp;", $data);
    $data = str_replace(' K ', " K&nbsp;", $data);
    $data = str_replace(' S ', " S&nbsp;", $data);
    $data = str_replace(' V ', " V&nbsp;", $data);
    $data = str_replace(' U ', " U&nbsp;", $data);
    $data = str_replace(' O ', " O&nbsp;", $data);
    $data = str_replace(' A ', " A&nbsp;", $data);
    $data = str_replace(' I ', " I&nbsp;", $data);
    return $data;
}


function v($termin) {
	$v = 'v';
	if (substr($termin, 0, 7) == 'středa') {
		$v .= 'e';
		$termin = str_replace('středa', 'středu', $termin);
	} elseif (substr($termin, 0, 8) == 'čtvrtek') {
		$v .= 'e';
	}
	$termin = str_replace(' ', '&nbsp;', $termin);
	return "$v&nbsp;$termin";
}
