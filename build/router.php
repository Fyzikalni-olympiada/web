<?php
/**
 * Router pro lokální náhled: php -S localhost:8000 build/router.php
 * Statické soubory servíruje přímo, hezké adresy směruje na index.php,
 * /data/terms.json a /rss.xml generuje za běhu.
 */

require dirname(__DIR__) . '/src/init.php';

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
    require ROOT_DIR . 'src/rss.php';
    return true;
}

if (preg_match('~^/terminy(?:-(a|bcd|ef))?\.ics$~', $path, $m)) {
    $GLOBALS['ICS_KATEGORIE'] = isset($m[1]) ? strtoupper($m[1]) : null;
    require ROOT_DIR . 'src/ics.php';
    return true;
}

/* Odešle soubor s content-type podle přípony (closure: router běží opakovaně v jednom procesu) */
$servuj = function ($soubor) {
    $typy = ['js' => 'text/javascript', 'css' => 'text/css', 'json' => 'application/json',
        'wasm' => 'application/wasm', 'svg' => 'image/svg+xml', 'png' => 'image/png',
        'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'ico' => 'image/x-icon',
        'woff2' => 'font/woff2', 'xml' => 'text/xml', 'txt' => 'text/plain',
        'webmanifest' => 'application/manifest+json'];
    $pripona = strtolower(pathinfo($soubor, PATHINFO_EXTENSION));
    header('Content-Type: ' . (isset($typy[$pripona]) ? $typy[$pripona] : 'application/octet-stream'));
    readfile($soubor);
    return true;
};

/* vyhledávací index generuje pagefind do dist/, mimo kořen repozitáře */
if (strpos($path, '/pagefind/') === 0 && is_file(__DIR__ . '/../dist' . $path)) {
    return $servuj(__DIR__ . '/../dist' . $path);
}

/* malé assety (css, js, pic, fonts, favicony, …) žijí v assets/, URL je bez prefixu */
if ($path !== '/' && strpos($path, '..') === false && is_file(ROOT_DIR . 'assets' . $path)) {
    return $servuj(ROOT_DIR . 'assets' . $path);
}

/* existující soubor (assety, archiv, …) servíruj přímo */
if ($path !== '/' && is_file(ROOT_DIR . ltrim($path, '/'))) {
    return false;
}

/* hezké adresy → index.php s PATH_INFO */
$_SERVER['PATH_INFO'] = $path;
require ROOT_DIR . 'src/index.php';
return true;
