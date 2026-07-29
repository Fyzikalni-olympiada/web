<?php
if (!defined("VALID_ACCESS")) {
    die("Neoprávněný přístup!");
}

/**
 * Datová vrstva: čte data/*.yaml a data/news/ (náhrada za MySQL).
 * Vše se načítá líně a drží v paměti procesu.
 */

use Symfony\Component\Yaml\Yaml;

require_once(ROOT_DIR . 'vendor/autoload.php');

/** @return array pathname => ['file' => ..., 'title' => ...] */
function data_routes()
{
    static $routes = null;
    if ($routes === null) {
        $routes = Yaml::parseFile(ROOT_DIR . 'data/files.yaml');
    }
    return $routes;
}

/** @return array filename => pathname (obrácená mapa k data_routes) */
function data_pathname_by_file()
{
    static $map = null;
    if ($map === null) {
        $map = [];
        foreach (data_routes() as $pathname => $r) {
            $map[$r['file']] = $pathname;
        }
    }
    return $map;
}

/** @return array strom menu (name, title?, file?|href?, children?) */
function data_menu()
{
    static $menu = null;
    if ($menu === null) {
        $menu = Yaml::parseFile(ROOT_DIR . 'data/menu.yaml');
    }
    return $menu;
}

/** Nekvótované datum v YAML parsuje Symfony jako unixový čas – normalizace na 'Y-m-d' */
function data_normalize_date($value)
{
    if (is_int($value)) {
        return gmdate('Y-m-d', $value);
    }
    return $value; // string nebo null
}

/**
 * Termíny ročníku z data/terms/<rocnik>.yaml: kategorie => [nazev, termin, date, misto?].
 * Kategorie: spolecne, A, BCD, EF, G (Archimediáda).
 */
function data_terms($rocnik)
{
    static $terms = [];
    if (!isset($terms[$rocnik])) {
        $file = ROOT_DIR . 'data/terms/' . (int) $rocnik . '.yaml';
        $kategorie = is_file($file) ? Yaml::parseFile($file) : [];
        foreach ($kategorie as &$list) {
            foreach ($list as &$t) {
                $t['date'] = data_normalize_date($t['date']);
            }
        }
        unset($list, $t);
        $terms[$rocnik] = $kategorie;
    }
    return $terms[$rocnik];
}

/**
 * Novinky pro studentský web, od nejnovější.
 * @return array[] ['id', 'date', 'time', 'subject', 'author', 'email', 'who',
 *                  'image'?, 'homepage', 'body']
 */
function data_news()
{
    static $news = null;
    if ($news === null) {
        $news = [];
        foreach (glob(ROOT_DIR . 'data/news/*.html') as $file) {
            $item = data_parse_front_matter($file);
            if (strpos($item['who'], 'student') === false) {
                continue;
            }
            $news[] = $item;
        }
        usort($news, function ($a, $b) {
            return [$b['date'], $b['time']] <=> [$a['date'], $a['time']];
        });
    }
    return $news;
}

/** @return array|null novinka podle id */
function data_news_by_id($id)
{
    foreach (data_news() as $item) {
        if ($item['id'] == $id) {
            return $item;
        }
    }
    return null;
}

/** @return array ['meta' + 'body'] zHTML souboru s YAML hlavičkou */
function data_parse_front_matter($file)
{
    $raw = file_get_contents($file);
    if (substr($raw, 0, 4) !== "---\n" || !($end = strpos($raw, "\n---\n", 4))) {
        throw new RuntimeException("Chybí YAML hlavička: $file");
    }
    $item = Yaml::parse(substr($raw, 4, $end - 4));
    $item['date'] = data_normalize_date($item['date']);
    $item['body'] = substr($raw, $end + 5);
    return $item;
}

/** @return array[] kořenové příspěvky fóra (vnořené odpovědi v 'children') */
function data_forum()
{
    static $forum = null;
    if ($forum === null) {
        $forum = Yaml::parseFile(ROOT_DIR . 'data/forum.yaml');
    }
    return $forum;
}
