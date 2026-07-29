<?php
/**
 * Router pro lokální náhled: php -S localhost:8000 build/router.php
 * Statické soubory servíruje přímo, hezké adresy směruje na index.php,
 * /data/terms.json a /data/thumbs.json generuje za běhu.
 */

$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$root = dirname(__DIR__);

if ($path === '/data/terms.json') {
    define('VALID_ACCESS', 1);
    define('ROOT_DIR', $root . '/');
    require_once $root . '/functions/data.php';
    require $root . '/build/terms_json.php';
    return true;
}

if ($path === '/data/thumbs.json') {
    header('Content-Type: application/json; charset=utf-8');
    echo is_file($root . '/temp/thumbs.json')
        ? file_get_contents($root . '/temp/thumbs.json') : '[]';
    return true;
}

if ($path === '/rss.xml') {
    require $root . '/rss.php';
    return true;
}

/* existující soubor (assety, archiv, …) servíruj přímo */
if ($path !== '/' && is_file($root . $path)) {
    return false;
}

/* hezké adresy → index.php s PATH_INFO */
$_SERVER['PATH_INFO'] = $path;
require $root . '/index.php';
return true;
