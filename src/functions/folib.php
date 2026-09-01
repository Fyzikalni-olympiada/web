<?php


/** Názvy měsíců v 2. pádě */
const MESICE = array(1 => 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince');

/* ----- Odkazy ----- */



/** URL stránky podle pathname z data/files.yaml */
function url_for_pathname($pathname)
{
    return '/' . ltrim($pathname, '/');
}



/** Odkaz na obsahový soubor (např. 'terminy.html' nebo 'html/terminy.html') */
function odkaz($souborName)
{
    $map = data_pathname_by_file();
    return isset($map[$souborName]) ? url_for_pathname($map[$souborName]) : '/';
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
    return '/';
}



/** Je aktuální stránka v podstromu položky? (cíl položky, nebo jeho podstránka) */
function menu_contains($node, $pathname)
{
    if (isset($node['file'])
            && ($node['file'] === $pathname || str_starts_with($pathname, $node['file'] . '/'))) {
        return true;
    }
    foreach (isset($node['children']) ? $node['children'] : [] as $child) {
        if (menu_contains($child, $pathname)) {
            return true;
        }
    }
    return false;
}



/** Cesta k css/js s verzí podle obsahu — po změně souboru si prohlížeče stáhnou novou
 *  (Cloudflare query string v cache klíči ignoruje, CDN to nerozbije) */
function asset($cesta)
{
    return $cesta . '?v=' . substr(md5_file(ROOT_DIR . 'assets/' . ltrim($cesta, '/')), 0, 8);
}



/** Inline SVG ikona položky menu (pic/menu/*.svg), ať jde stylovat přes CSS */
function menu_ikona($node)
{
    if (!isset($node['ikona'])) {
        return '';
    }
    $svg = file_get_contents(__DIR__ . '/../../assets/pic/menu/' . $node['ikona'] . '.svg');
    return str_replace('<svg ', '<svg class="ikona-menu" ', trim($svg));
}



function menu($current)
{
    $strHTML = '
                <ul class="tabbed">';

    foreach (data_menu() as $node) {
        $url = menu_target($node);
        $title = isset($node['title']) ? $node['title'] : $node['name'];
        $selected = menu_contains($node, $current);

        $dropdown = ($submenu = submenu($node, $current));
        $strHTML .= '
			<li class="' . ($selected ? 'current-tab' : ($dropdown ? 'dropdown' : '')) . '">
			<a href="' . $url . '" title="' . $title . '"' . (isset($node['href']) ? ' target="_blank"' : '') . ($dropdown ? ' role="button" class="dropdown-toggle" data-toggle="dropdown"' : '') . '>' . menu_ikona($node) . $node['name'] . ($dropdown ? '<b class="caret"></b>' : '') . '</a>';
        if ($dropdown) {
            $strHTML .= $submenu;
        }
        $strHTML .= ' </li>';
    }
    $strHTML .= '
                </ul>';

    return $strHTML;
}



/** Podpoložky aktivní položky menu pro lištu pod hlavičkou; bez aktivní položky prázdné */
function navbar($current)
{
    $aktivni = null;
    foreach (data_menu() as $node) {
        if (!empty($node['children']) && menu_contains($node, $current)) {
            $aktivni = $node;
            break;
        }
    }
    if ($aktivni === null) {
        if ($current !== '/') {
            return '';
        }
        $aktivni = data_menu()[0]; // homepage ukazuje podpoložky první položky (Soutěž)
    }

    $strHTML = '
		<ul>';
    foreach ($aktivni['children'] as $child) {
        $strHTML .= '
			<li' . (menu_contains($child, $current) ? ' class="active"' : '') . '><a href="' . menu_target($child) . '" title="' . (isset($child['title']) ? $child['title'] : $child['name']) . '"' . (isset($child['href']) ? ' target="_blank"' : '') . '>' . $child['name'] . '</a></li>';
    }
    return $strHTML . '
		</ul>';
}



function submenu($node, $current)
{
    if (empty($node['children'])) {
        return '';
    }
    $strHTML = '
                <ul class="dropdown-menu" role="menu">';

    foreach ($node['children'] as $child) {
        $url = menu_target($child);
        $title = isset($child['title']) ? $child['title'] : $child['name'];
        $selected = menu_contains($child, $current) ? ' class="current-tab"' : '';

        $strHTML .= '
				<li' . $selected . '><a href="' . $url . '" title="' . $title . '"' . (isset($child['href']) ? ' target="_blank"' : '') . $selected . '>' . $child['name'] . '</a></li>';
    }
    $strHTML .= '
                </ul>';
    return $strHTML;
}

/* ----- Termíny ----- */



/** Termíny dané kategorie aktuálního ročníku */
function terms_kategorie($kategorie)
{
    $terms = data_terms(AKTUALNI_ROCNIK);
    return isset($terms[$kategorie]) ? $terms[$kategorie] : array();
}



/** Termíny podle podřetězce názvu, volitelně kategorie */
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

/** První termín dané kategorie odpovídající názvu (viz terms_hledej), jinak null */
function termin($kategorie, $nazev)
{
	$rows = terms_hledej($nazev, $kategorie);
	return $rows ? $rows[0] : null;
}



/* ----- Novinky ----- */



/**
 * HTML jedné novinky. $permalink: datum jako odkaz na /novinka/<id>
 * (false = samostatná stránka novinky, titulek je pak h1);
 * $poradi: číslovaná kotva v seznamu novinek.
 */
function novinka_html($item, $permalink = true, $poradi = null)
{
	list($rok, $mesic, $den) = explode('-', $item['date']);
	$datum = preg_replace('~^0~', '', $item['time']) . ', ' . (int) $den . '. ' . MESICE[(int) $mesic] . ' ' . $rok;
	$img = isset($item['image']) ? $item['image'] : null;
	$h = $permalink ? 'h3' : 'h1';

	$s = '
        <div class="post">

            <div class="archive-post-title">' . ($poradi !== null ? '<a name="' . $poradi . '"></a>' : '') . '
                <' . $h . '>' . $item['subject'] . '</' . $h . '>
                <p>';
	if ($img && $img['align'] !== 'block') {
		$s .= '
					<img src="/data/news/' . $img['filename'] . '" style="margin:'
			. (isset($img['vspace']) ? $img['vspace'] . 'px ' : '5px ')
			. (isset($img['hspace']) ? $img['hspace'] . 'px ' : '7px ')
			. ';float: ' . $img['align'] . '; ' . ($img['align'] == 'left' ? 'margin-left: 0px;' : '')
			. '" alt="' . $img['alt'] . '" title="' . $img['alt'] . '"/>';
	}
	$s .= $item['body'] . '</p>';
	if ($img && $img['align'] === 'block') {
		$s .= '
				<div class="center-box">
					<img src="/data/news/' . $img['filename'] . '" class="img-responsive"
					alt="' . $img['alt'] . '" title="' . $img['alt'] . '"/>
				</div>';
	}
	$s .= '
				<div class="post-date">
					' . ($permalink
						? '<a href="/novinka/' . $item['id'] . '" title="Trvalý odkaz na příspěvek" class="link">' . $datum . '</a>'
						: '<span class="link">' . $datum . '</span>') . '
                    <span title="id=' . $item['id'] . '">&bull;</span>
                    <a href="mailto:' . $item['email'] . '" title="Autor příspěvku" class="sign">' . $item['author'] . '</a>
				</div>
            </div>

            <div class="clearer">&nbsp;</div>

        </div>';
	return $s;
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
    $zdroj = ROOT_DIR . soubor_obsahu($dir);
    $csv = $zdroj . '/popisky.csv';
    if (is_file($csv)) {
        $handle = fopen($csv, 'r');
        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            $popisky[$data[0]] = $data[1];
        }
        fclose($handle);
    }

    echo '
<div id="photoGallery">';

    $nahledy = array_flip(scandir($zdroj . '/thumbnails'));
    foreach (glob($zdroj . '/photos/*') as $foto) {
        $soubor = basename($foto);
        if (!isset($nahledy[$soubor])) {
            continue;
        }
        /* titulek podle data v názvu souboru; když v názvu datum není, zbývá jen popisek */
        if ($dny !== null && preg_match('~^(\d\d)_(\d\d)(\d\d)(\d\d)~', $soubor, $m)) { // DD_HHMMSS…
            $title = (isset($dny[$m[1]]) ? $dny[$m[1]] : '') . ', ' . $m[2] . ':' . $m[3] . ':' . $m[4];
        } elseif (preg_match('~((?:19|20)\d\d)(\d\d)(\d\d)~', $soubor, $m)) { // …YYYYMMDD…
            $title = ltrim($m[3], '0') . '. ' . ltrim($m[2], '0') . '. ' . $m[1];
        } else {
            $title = '';
        }
        if (!empty($popisky[$soubor])) {
            $title = ($title !== '' ? $title . ' &ndash; ' : '') . $popisky[$soubor];
        }
        echo '
	<div class="photoContainer">
		<a href="/' . $dir . '/photos/' . $soubor . '" title="' . $title . '">
		<img src="/' . $dir . '/thumbnails/' . $soubor . '" alt="' . $title . '" loading="lazy" />
		</a>
	</div>';
    }

    echo '
<div style="clear: left"></div>
</div>';
}






/**
 * Route podle PATH_INFO: napln (soubor k include; 404 = stránka neexistuje),
 * nadpis (jen pro <title>), pathname a parametry speciálních stránek (novinky, diskuse).
 */
function parsuj()
{
	$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
	$pathname = ltrim($path_info, '/') ?: '/';

	$route = array(
		'pathname' => $pathname,
		'popis' => null,  /* obsah <meta name="description"> */
		'news_archiv' => false,
		'novinka' => null,
		'forum_who' => null,
		'forum_page' => 1,
		'forum_thread' => null,
	);

	/* Zvláštní stránky mimo data/files.yaml */
	if ($pathname === 'archiv-novinek') {
		return array('nadpis' => 'Archiv novinek', 'napln' => FILE_NEWS, 'news_archiv' => true) + $route;
	}
	if (preg_match('~^novinka/(\d+)$~', $pathname, $m)
			&& ($novinka = data_news_by_id((int) $m[1])) !== null) {
		return array('nadpis' => $novinka['subject'], 'napln' => 'src/content/novinka.php',
			'popis' => zkrat_popis($novinka['body']), 'novinka' => $novinka) + $route;
	}
	if (preg_match('~^diskuse(-ucitele)?(?:/(\d+))?$~', $pathname, $m)) {
		return array('nadpis' => 'Diskusní fórum', 'napln' => FILE_FORUM,
			'forum_who' => empty($m[1]) ? 'student' : 'ucitel',
			'forum_page' => empty($m[2]) ? 1 : (int) $m[2]) + $route;
	}
	if (preg_match('~^diskuse/vlakno/(\d+)$~', $pathname, $m)) {
		return array('nadpis' => 'Diskusní fórum', 'napln' => FILE_FORUM, 'forum_thread' => (int) $m[1]) + $route;
	}

	$routes = data_routes();
	$napln = isset($routes[$pathname]) ? soubor_obsahu($routes[$pathname]['file']) : null;
	if ($napln !== null && file_exists(ROOT_DIR . $napln)) {
		return array('nadpis' => $routes[$pathname]['title'], 'napln' => $napln,
			'popis' => isset($routes[$pathname]['description']) ? $routes[$pathname]['description'] : null) + $route;
	}
	return array('nadpis' => 'Stránka nenalezena', 'napln' => 404) + $route;
}



/** Data používají logické cesty (archiv/…, tana/…); šablony leží v html/, obsah ve files/ */
function soubor_obsahu($cesta)
{
	if (file_exists(ROOT_DIR . $cesta)) {
		return $cesta;
	}
	if (file_exists(ROOT_DIR . 'files/' . $cesta)) {
		return 'files/' . $cesta;
	}
	return 'html/' . $cesta;
}



/** Prostý text pro meta description – z HTML, oříznutý na hranici slova */
function zkrat_popis($html, $limit = 155)
{
	$text = trim(preg_replace('~\s+~u', ' ',
		html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
	$text = str_replace("\u{a0}", ' ', $text);
	if (mb_strlen($text) > $limit) {
		$text = preg_replace('~\s+\S*$~u', '', mb_substr($text, 0, $limit)) . '…';
	}
	return $text;
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
