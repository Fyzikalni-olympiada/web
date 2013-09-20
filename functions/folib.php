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
    //$dni = Array('', 'neděle','pondělí','úterý','středa','čtvrtek','pátek','sobota');
    return $day . '. ' . $monthz[$month] . ' ' . $year;
}

/* ----- Odkazy ----- */



/**
 * Nepoužívat, jen kvůli BC
 * @deprecated
 * @param string $souborName
 * @param string $kdo
 * @param int $amp_entity
 * @return string
 */
function odkaz($souborName = null, $kdo = null, $amp_entity = 1)
{
    if (is_null($kdo)) {
        $r = odkaz2($souborName, null, $amp_entity, true);
    } else {
        $r = odkaz2($souborName, array('who' => $kdo), $amp_entity, true);
    }

    if (strpos($souborName, 'content/listina.php') && strpos($r, '?') === FALSE) {
        $r .= '?';
    }

    return $r;
}



function odkaz2($path = null, $addget = null, $amp_entity = 1, $erase = false)
{
    if ($erase) {
        $get['file'] = $_GET['file'];
        if (isset($_GET['who'])) {
            $get['who'] = $_GET['who'];
        }
        $get = (array) $addget + $get;
    } elseif (!is_null($addget)) {
        $get = array_merge($_GET, $addget);
    } else {
        $get = $_GET;
    }

    $pathname = false;

    if (!is_null($path)) {
        $GLOBALS['mysql_odkazy']->query("
                        SELECT `id`, pathname
                        FROM `" . TABLE_FILES . "`
                        WHERE `filename`=" . $GLOBALS['mysql_odkazy']->escape_string($path) . "
                        OR `filename`=" . $GLOBALS['mysql_odkazy']->escape_string("html/" . $path) . "
                ");
        if ($row = $GLOBALS['mysql_odkazy']->fetch_array()) {
            $get['file'] = $row['id'];
            $pathname = $row['pathname'];
        }
    } else {
        $GLOBALS['mysql_odkazy']->query("
                        SELECT pathname
                        FROM `" . TABLE_FILES . "`
                        WHERE `id`=" . $GLOBALS['mysql_odkazy']->escape_string($get['file']) . "
                ");
        if ($row = $GLOBALS['mysql_odkazy']->fetch_array()) {
            $pathname = $row['pathname'];
        }
    }
    if ($amp_entity) {
        $amp = '&amp;';
    } else {
        $amp = '&';
    }

    if ($pathname) {
        unset($get['file']);
        $url = ROOT_WWW/* . DIR_INDEX*/ . ltrim($pathname, '/');
    } else {
        $url = ROOT_WWW/* . DIR_INDEX*/;
    }

    if (isset($get['who']) && $get['who'] == 'student') {
        unset($get['who']);
    }

    $query = array();
    foreach ($get as $name => $value) {
        if (!empty($value) || $value === 0 || $value === '0') {
            $query[] = $name . '=' . $value;
        }
    }
    $url = ($query ? trim($url, '?') . '?' . implode($amp, $query) : $url );

    return $url;
}

/* ----- Menu ----- */



function menu()
{
    $strHTML = '
                <ul class="tabbed">';
    $GLOBALS['mysql_odkazy']->query("
        SELECT id, poradi, type, file_id, href, name, title
        FROM " . TABLE_MENU_STRUCTURE . "
        WHERE parent_id IS NULL
        AND path='" . FILE_INDEX . "'
        ORDER BY poradi
    ");
    $result = $GLOBALS['mysql_odkazy']->vysledek;

	$i = 1;
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        if (empty($row['type'])) { //zjistime, na jaky soubor mame vlastne odkazovat
            $GLOBALS['mysql_odkazy']->query("
                SELECT poradi, type, file_id, href
                FROM " . TABLE_MENU_STRUCTURE . "
                WHERE parent_id=" . $row['id'] . "
                AND path='" . FILE_INDEX . "'
                ORDER BY poradi
                LIMIT 1
            ");
            if ($subrow = $GLOBALS['mysql_odkazy']->fetch_array())
                $row = array_merge($row, $subrow);
        }

        if ($row['type'] == 'file')
            $url = odkaz2(null, array('file' => $row['file_id']));
        else
            $url = $row['href'];
        if ($row['id'] == $GLOBALS['parentID'] || $row['id'] == $GLOBALS['structureID'])
            $selected = true;
        else
            $selected = false;

		if ($row['file_id'] == 19) {//diskuse
			$urlS = odkaz2(null, array('file' => $row['file_id'], 'who' => 'student'));
			$urlU = odkaz2(null, array('file' => $row['file_id'], 'who' => 'ucitel'));
			$strHTML .= '
				<li class="dropdown' . ($selected ? ' current-tab' : '') . ($i++ > 5 ? ' smaller' : '') . '">
				<a href="' . $url . '" title="' . $row['title'] . '" id="diskuse-drop" role="button" class="dropdown-toggle" data-toggle="dropdown">' . $row['name'] . '<b class="caret"></b></a>
				<ul class="dropdown-menu" role="menu" aria-labelledby="diskuse-drop">
					<li><a href="' . $urlS . '">Studenti</a></li>
					<li><a href="' . $urlU . '">Učitelé</a></li>
				</ul>';
		} else {
			$dropdown = ($submenu = submenu($row['id'], true));
			$strHTML .= '
			<li class="' . ($selected ? 'current-tab' : ($dropdown ? 'dropdown' : '')) . ($i++ > 5 ? ' smaller' : '') . '">
			<a href="' . $url . '" title="' . $row['title'] . '"' . ($dropdown ? ' role="button" class="dropdown-toggle" data-toggle="dropdown"' : '') . '>' . $row['name'] . ($dropdown ? '<b class="caret"></b>' : '') . '</a>';
			if ($dropdown) {
				$strHTML .= $submenu;
			}
		}
		$strHTML .= ' ' . MENU_ODDELOVAC . '</li>';

    }
    $strHTML[strlen($strHTML) - 6] = ' ';
    $strHTML .= '
                </ul>';

    return $strHTML;
}



function submenu($parentID = 1, $dropdown = false)
{
    $strHTML = '
                <ul ' . ($dropdown ? 'class="dropdown-menu" role="menu"' : 'class="tabbed"') . '>';
    $result = $GLOBALS['mysql_odkazy']->query("
        SELECT id, poradi, type, file_id, href, name, title
        FROM " . TABLE_MENU_STRUCTURE . "
        WHERE parent_id='" . $parentID . "'
        AND path='" . FILE_INDEX . "'
        ORDER BY poradi
    ");

    while ($row = mysql_fetch_assoc($result)) {
        if ($row['type'] == 'file')
            $url = odkaz2(null, array('file' => $row['file_id']));
        else
            $url = $row['href'];
        if (isset($GLOBALS['structureID']) && $row['id'] == $GLOBALS['structureID'])
            $selected = ' class="current-tab"';
        else
            $selected = '';

		$strHTML .= '
				<li' . $selected . '>' . SUBMENU_ODRAZKA . '<a href="' . $url . '" title="' . $row['title'] . '"' . $selected . '>' . $row['name'] . '</a></li>';
    }
    $strHTML .= '
                </ul>';
    return isset($url) ? $strHTML : '';
}



function novinka($poradi)
{
    $monthz = Array(1 => 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince');

    $GLOBALS['mysql']->query('
        SELECT id, YEAR(date) AS year, MONTH(date) AS month, DAYOFMONTH(date) AS day, date, time, subject, text, pic_id, name, email
        FROM news
        WHERE wheres = \'' . NOVINKY_WHERES . '\'
        AND who LIKE \'%' . $GLOBALS['who'] . '%\'
        ORDER BY date DESC, time DESC
        LIMIT ' . intval($poradi - 1) . ', 1
    ', 0);
    $result = $GLOBALS['mysql']->vysledek;
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    eval("\$row[\"text\"] = \"$row[text]\";");
    $texT = preg_replace("/^((.+?\s+){1,15}).*/s", '\\1', strip_tags($row['text']));

    $strHTML = '';
    $strHTML .= '<h2 class="title">' . $row["subject"] . '</h2>';
    $strHTML .= '<p><span class="date">' . $row["day"] . '. ' . $monthz[$row["month"]] . ' ' . $row["year"] . '</span> &mdash; ' . $texT . ' <a href="' . odkaz('content/news.php') . '#' . $poradi . '">Více...</a></p>';
    return $strHTML;
}



function novinky()
{
    $monthz = Array(1 => 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince');
    $strHTML = '
        <ul class="nice-list">';

    $GLOBALS['mysql']->query('
        SELECT id, YEAR(date) AS year, MONTH(date) AS month, DAYOFMONTH(date) AS day, date, time, subject, text, pic_id, name, email
        FROM news
        WHERE DATE_SUB(CURDATE(),INTERVAL ' . NOVINKY_INTERVAL . ' DAY) <= date
        AND wheres = \'' . NOVINKY_WHERES . '\'
        AND who LIKE \'%' . $GLOBALS['who'] . '%\'
        ORDER BY date DESC, time DESC
        LIMIT ' . NOVINKY_NA_STRANKU . '
    ', 0);
    $result = $GLOBALS['mysql']->vysledek;
    $poradi = 0;
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $strHTML .= '
            <li>
                <div class="left"><a href="' . odkaz('content/news.php') . '#' . ++$poradi . '">' . $row["subject"] . '</a></div>
                <div class="right">' . $row["day"] . '. ' . $monthz[$row["month"]] . '</div>
                <div class="clearer">&nbsp;</div>
            </li>';
    }
    return $strHTML;
}

require_once(ROOT_DIR . 'libs/Nette/Utils/exceptions.php');
require_once(ROOT_DIR . 'libs/Nette/Utils/Framework.php');
require_once(ROOT_DIR . 'libs/Nette/Utils/ObjectMixin.php');
require_once(ROOT_DIR . 'libs/Nette/Utils/Object.php');
require_once(ROOT_DIR . 'libs/Nette/Utils/Iterators/CallbackFilterIterator.php');
require_once(ROOT_DIR . 'libs/Nette/Utils/Iterators/RecursiveCallbackFilterIterator.php');
require_once(ROOT_DIR . 'libs/Nette/Utils/Iterators/MapIterator.php');
require_once(ROOT_DIR . 'libs/Nette/Utils/Tools.php');
require_once(ROOT_DIR . 'libs/Nette/Utils/Finder.php');
require_once(ROOT_DIR . 'libs/Nette/Caching/Cache.php');
require_once(ROOT_DIR . 'libs/Nette/Caching/ICacheStorage.php');
require_once(ROOT_DIR . 'libs/Nette/Caching/FileStorage.php');
require_once(ROOT_DIR . 'libs/Nette/Caching/ICacheJournal.php');
require_once(ROOT_DIR . 'libs/Nette/Caching/FileJournal.php');
require_once(ROOT_DIR . 'libs/Nette/Utils/Image.php');



function rand_thumb()
{
    function getPath($f)
    {
        return $f->getPath();
    }

    $cache = new NCache(new NFileStorage(ROOT_DIR . 'temp', new NFileJournal(ROOT_DIR . 'temp')), 'folib');
    if (isset($cache['thumbs'])) {
        $thumbs = $cache['thumbs'];
    } else {
        $thumbs = iterator_to_array(new NMapIterator(NFinder::findFiles('*.jpg')->size('>27kB')->from(ROOT_DIR)->exclude('temp', 'thumbnails', '_ukrajina', '_norsko'), 'getPath'));
        $cache->save('thumbs', $thumbs);
    }

    $s = '';
    for ($i = 0; $i < 5; $i++) {
        $rand_pic = array_rand($thumbs);
        $rand_thumb = ROOT_DIR . 'temp/images/' . md5($rand_pic) . '.jpg';
        if (!is_file($rand_thumb)) {
            $image = NImage::fromFile($rand_pic);
            $image->resize(260, 260, NImage::FIT);
            //$image->sharpen();
            $image->save($rand_thumb, 95);
        }
        $path_pic = ROOT_WWW . substr($rand_pic, strlen(ROOT_DIR));
        $path_thumb = ROOT_WWW . substr($rand_thumb, strlen(ROOT_DIR));


        $s .= "<a href=\"$path_pic\"" . ($i == 0 ? '' : ' style="display: none;"') . "><img src=\"$path_thumb\" alt=\"Ze života Fyzikální olympiády\"/></a>";
    }

    return $s;
}



function nadpis()
{
    $headline = '';
    /* $GLOBALS['mysql_odkazy']->query("
      SELECT name
      FROM " . TABLE_MENU_STRUCTURE . "
      WHERE id='" . $GLOBALS['parentID'] . "'
      ");
      if ($row = $GLOBALS['mysql_odkazy']->fetch_array())
      $headline = $row['name'] . ' :: ';
     */

    return $headline . $GLOBALS['nadpis'];
}



function text()
{

}



/**
 * Parsovani GET pozadavku
 */
function parsuj()
{
    setlocale(LC_CTYPE, 'cs_CZ.utf8');
    if (isset($_SERVER['PATH_INFO']) || !isset($_GET['file'])) {
		$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        if ($pos = strpos($path_info, '/', 0) !== FALSE)
            $pathname = substr($path_info, $pos) or $pathname = '/';
        else
            $pathname = '/';

        $pathname = iconv('UTF-8', 'ASCII//TRANSLIT', $pathname);

		// 301
		if ($pathname === 'archiv/studijni-texty') {
			header('Location: /studijni-texty', TRUE, 301);
			exit();
		}
		if ($pathname === 'novinky') {
			header('Location: /', TRUE, 301);
			exit();
		}
        $row = $GLOBALS['mysql_odkazy']->query('
                    SELECT id
                    FROM ' . TABLE_FILES . '
                    WHERE pathname=' . $GLOBALS['mysql_odkazy']->escape_string($pathname) . '
                    AND (dir_index="' . SITE . '"
                        OR dir_index="")
                    ORDER BY dir_index DESC
            ');
        $result = $GLOBALS['mysql_odkazy']->vysledek;
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $_GET["file"] = $row['id'];
	}
	$GLOBALS['mysql_odkazy']->query("
        SELECT filename, " . TABLE_FILES . ".title, " . TABLE_MENU_STRUCTURE . ".id, parent_id
        FROM " . TABLE_FILES . " LEFT JOIN " . TABLE_MENU_STRUCTURE . "
        ON " . TABLE_FILES . ".id=" . TABLE_MENU_STRUCTURE . ".file_id
        WHERE " . TABLE_FILES . ".id=" . $GLOBALS['mysql_odkazy']->escape_string($_GET["file"]) . "
        AND " . TABLE_MENU_STRUCTURE . ".path='" . FILE_INDEX . "'
    ");
    $result = $GLOBALS['mysql_odkazy']->vysledek;
    if ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $GLOBALS['mysql_odkazy']->query("
            SELECT parent_id, id
            FROM " . TABLE_MENU_STRUCTURE . "
            WHERE id='" . $row['parent_id'] . "'
            AND path='" . FILE_INDEX . "'
        ");
        if ($subrow = $GLOBALS['mysql_odkazy']->fetch_array()) {//file je soucasti menu_structure
            if ($subrow['parent_id'] == NULL) { //file je soucasti submenu
                $GLOBALS['parentID'] = $row['parent_id'];
                $GLOBALS['structureID'] = $row['id'];
            } else { //neni soucasti submenu
                $GLOBALS['parentID'] = $subrow['parent_id'];
                $GLOBALS['structureID'] = $row['parent_id'];
            }
        } else {
            $GLOBALS['parentID'] = NULL;
            $GLOBALS['structureID'] = $row['id'];
        }
	} else {
		$GLOBALS['parentID'] = NULL;
		$GLOBALS['structureID'] = NULL;
	}

    /* Soubor bude zobrazen i kdyz neni ve stromu menu */
    $GLOBALS['mysql_odkazy']->query("
        SELECT filename, title, difference
        FROM " . TABLE_FILES . "
        WHERE " . TABLE_FILES . ".id=" . $GLOBALS['mysql_odkazy']->escape_string($_GET["file"]) . "
    ");
    $result = $GLOBALS['mysql_odkazy']->vysledek;
    if (($row = mysql_fetch_array($result, MYSQL_ASSOC)) && file_exists(ROOT_DIR . $row["filename"])) {
		$GLOBALS['nadpis'] = $row["title"];
		$GLOBALS['napln'] = $row["filename"];
		$GLOBALS['difference'] = $row['difference'];
	} else {
		header("HTTP/1.1 404 Not Found");
		$GLOBALS['nadpis'] = 'Nenalezeno';
		$GLOBALS['napln'] = NULL;
		$GLOBALS['parentID'] = 1;
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



function latest_terms()
{
    $GLOBALS['mysql']->query('
        SELECT nazev, termin, UNIX_TIMESTAMP(date) AS timestamp, duvernost
        FROM ' . TABLE_TERMS . '
        WHERE kategorie IN (0, 1)
        AND rocnik="' . AKTUALNI_ROCNIK . '"
        AND DATE >= NOW()
        AND duvernost="public"
        ORDER BY date
        LIMIT 1
    ');
    if ($row = $GLOBALS['mysql']->fetch_array()) {
        $r['Kategorie A'] = '<div class="left">' . $row['nazev'] . '</div><div class="right">' . $row['termin'] . '</div>';
    }

    $GLOBALS['mysql']->query('
        SELECT nazev, termin, UNIX_TIMESTAMP(date) AS timestamp, duvernost
        FROM ' . TABLE_TERMS . '
        WHERE kategorie IN (0, 2)
        AND rocnik="' . AKTUALNI_ROCNIK . '"
        AND DATE >= NOW()
        AND duvernost="public"
        ORDER BY date
        LIMIT 1
    ');
    if ($row = $GLOBALS['mysql']->fetch_array()) {
        $r['Kategorie B&ndash;D'] = '<div class="left">' . $row['nazev'] . '</div><div class="right">' . $row['termin'] . '</div>';
    }

    $GLOBALS['mysql']->query('
        SELECT nazev, termin, UNIX_TIMESTAMP(date) AS timestamp, duvernost
        FROM ' . TABLE_TERMS . '
        WHERE kategorie IN (0, 3)
        AND rocnik="' . AKTUALNI_ROCNIK . '"
        AND DATE >= NOW()
        AND duvernost="public"
        ORDER BY date
        LIMIT 1
    ');
    if ($row = $GLOBALS['mysql']->fetch_array()) {
        $r['Kategorie E, F'] = '<div class="left">' . $row['nazev'] . '</div><div class="right">' . $row['termin'] . '</div>';
    }

    $GLOBALS['mysql']->query('
        SELECT nazev, termin, UNIX_TIMESTAMP(date) AS timestamp, duvernost
        FROM ' . TABLE_TERMS . '
        WHERE kategorie = 4
        AND rocnik="' . AKTUALNI_ROCNIK . '"
        AND DATE >= NOW()
        AND duvernost="public"
        ORDER BY date
        LIMIT 1
    ');
    if ($row = $GLOBALS['mysql']->fetch_array()) {
        $r['Archimediáda'] = '<div class="left">' . $row['nazev'] . '</div><div class="right">' . $row['termin'] . '</div>';
    }

    return $r;
}




if (!function_exists('imageconvolution')) {
    function imageconvolution($src, $filter, $filter_div, $offset)
    {
        if ($src == NULL) {
            return 0;
        }

        $sx = imagesx($src);
        $sy = imagesy($src);
        $srcback = ImageCreateTrueColor($sx, $sy);
        ImageCopy($srcback, $src, 0, 0, 0, 0, $sx, $sy);

        if ($srcback == NULL) {
            return 0;
        }

        for ($y = 0; $y < $sy; ++$y) {
            for ($x = 0; $x < $sx; ++$x) {
                $new_r = $new_g = $new_b = 0;
                $alpha = imagecolorat($srcback, $pxl[0], $pxl[1]);
                $new_a = $alpha >> 24;

                for ($j = 0; $j < 3; ++$j) {
                    $yv = min(max($y - 1 + $j, 0), $sy - 1);
                    for ($i = 0; $i < 3; ++$i) {
                        $pxl = array(min(max($x - 1 + $i, 0), $sx - 1), $yv);
                        $rgb = imagecolorat($srcback, $pxl[0], $pxl[1]);
                        $new_r += ( ($rgb >> 16) & 0xFF) * $filter[$j][$i];
                        $new_g += ( ($rgb >> 8) & 0xFF) * $filter[$j][$i];
                        $new_b += ( $rgb & 0xFF) * $filter[$j][$i];
                    }
                }

                $new_r = ($new_r / $filter_div) + $offset;
                $new_g = ($new_g / $filter_div) + $offset;
                $new_b = ($new_b / $filter_div) + $offset;

                $new_r = ($new_r > 255) ? 255 : (($new_r < 0) ? 0 : $new_r);
                $new_g = ($new_g > 255) ? 255 : (($new_g < 0) ? 0 : $new_g);
                $new_b = ($new_b > 255) ? 255 : (($new_b < 0) ? 0 : $new_b);

                $new_pxl = ImageColorAllocateAlpha($src, (int) $new_r, (int) $new_g, (int) $new_b, $new_a);
                if ($new_pxl == -1) {
                    $new_pxl = ImageColorClosestAlpha($src, (int) $new_r, (int) $new_g, (int) $new_b, $new_a);
                }
                if (($y >= 0) && ($y < $sy)) {
                    imagesetpixel($src, $x, $y, $new_pxl);
                }
            }
        }
        imagedestroy($srcback);
        return 1;
    }

}
