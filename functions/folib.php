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
    if (isset($map[$souborName])) {
        return url_for_pathname($map[$souborName]);
    }
    if (isset($map['html/' . $souborName])) {
        return url_for_pathname($map['html/' . $souborName]);
    }
    return '/';
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



function menu($current)
{
    $strHTML = '
                <ul class="tabbed">';

    $i = 1;
    foreach (data_menu() as $node) {
        $url = menu_target($node);
        $title = isset($node['title']) ? $node['title'] : $node['name'];
        $selected = menu_contains($node, $current);

        $dropdown = ($submenu = submenu($node, $current));
        $strHTML .= '
			<li class="' . ($selected ? 'current-tab' : ($dropdown ? 'dropdown' : '')) . ($i++ > 5 ? ' smaller' : '') . '">
			<a href="' . $url . '" title="' . $title . '"' . ($dropdown ? ' role="button" class="dropdown-toggle" data-toggle="dropdown"' : '') . '>' . $node['name'] . ($dropdown ? '<b class="caret"></b>' : '') . '</a>';
        if ($dropdown) {
            $strHTML .= $submenu;
        }
        $strHTML .= ' </li>';
    }
    $strHTML .= '<li><a title="Odevzdávací systém Fyzikální olympiády"
		target="_blank" href="https://osmo.fyzikalniolympiada.cz/">Osmo</a></li>';
    $strHTML .= '<li class="jen-mobil"><a title="Webové stránky krajských komisí"
		href="/stranky-regionu">Krajské stránky</a></li>';
    $strHTML .= '
                </ul>';

    return $strHTML;
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
        $selected = (isset($child['file']) && $child['file'] === $current) ? ' class="current-tab"' : '';

        $strHTML .= '
				<li' . $selected . '><a href="' . $url . '" title="' . $title . '"' . $selected . '>' . $child['name'] . '</a></li>';
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
 * HTML jedné novinky. $permalink: datum jako odkaz na /novinka/<id>;
 * $poradi: číslovaná kotva v seznamu novinek.
 */
function novinka_html($item, $permalink = true, $poradi = null)
{
	list($rok, $mesic, $den) = explode('-', $item['date']);
	$datum = preg_replace('~^0~', '', $item['time']) . ', ' . (int) $den . '. ' . MESICE[(int) $mesic] . ' ' . $rok;
	$img = isset($item['image']) ? $item['image'] : null;

	$s = '
        <div class="post">

            <div class="archive-post-title">' . ($poradi !== null ? '<a name="' . $poradi . '"></a>' : '') . '
                <h3>' . $item['subject'] . '</h3>
                <p>';
	if ($img && $img['align'] !== 'block') {
		$s .= '
					<img src="/upload/' . $img['filename'] . '" style="margin:'
			. (isset($img['vspace']) ? $img['vspace'] . 'px ' : '5px ')
			. (isset($img['hspace']) ? $img['hspace'] . 'px ' : '7px ')
			. ';float: ' . $img['align'] . '; ' . ($img['align'] == 'left' ? 'margin-left: 0px;' : '')
			. '" alt="' . $img['alt'] . '" title="' . $img['alt'] . '"/>';
	}
	$s .= $item['body'] . '</p>';
	if ($img && $img['align'] === 'block') {
		$s .= '
				<div class="center-box">
					<img src="/upload/' . $img['filename'] . '" class="img-responsive"
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
		return array('nadpis' => $novinka['subject'], 'napln' => 'content/novinka.php', 'novinka' => $novinka) + $route;
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
	if (isset($routes[$pathname]) && file_exists(ROOT_DIR . $routes[$pathname]['file'])) {
		return array('nadpis' => $routes[$pathname]['title'], 'napln' => $routes[$pathname]['file']) + $route;
	}
	return array('nadpis' => 'Stránka nenalezena', 'napln' => 404) + $route;
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
