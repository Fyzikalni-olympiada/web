<?php
/**
 * Router pro lokální náhled: php -S localhost:8000 build/router.php
 * Statické soubory servíruje přímo, hezké adresy směruje na index.php,
 * /data/terms.json a /rss.xml generuje za běhu.
 */

require dirname(__DIR__) . '/init.php';

$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

/* PHP zdrojáky se nikdy neservírují (produkce žádné nemá) */
if (str_ends_with($path, '.php')) {
    http_response_code(404);
    return true;
}

if ($path === '/data/terms.json') {
    require __DIR__ . '/terms_json.php';
    return true;
}

if ($path === '/rss.xml') {
    require ROOT_DIR . 'rss.php';
    return true;
}

if ($path === '/terminy.ics') {
    require ROOT_DIR . 'ics.php';
    return true;
}

/* existující soubor (assety, archiv, …) servíruj přímo */
if ($path !== '/' && is_file(ROOT_DIR . ltrim($path, '/'))) {
    return false;
}

/* hezké adresy → index.php s PATH_INFO */
$_SERVER['PATH_INFO'] = $path;
require ROOT_DIR . 'index.php';
return true;
