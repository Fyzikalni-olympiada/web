<?php
/**
 * Sestaví statický web do dist/.
 *
 * Použití: php build/build.php
 *
 * - stránky z data/files.yaml -> dist/<pathname>.html (hosting servíruje bez přípony)
 * - novinky (/novinka/<id>, /archiv-novinek), diskuse (seznamy + vlákna), 404
 * - rss.xml, data/terms.json
 * - kopie statických adresářů
 */

require dirname(__DIR__) . '/src/init.php';

$DIST = ROOT_DIR . 'dist/';

/* Adresáře a soubory kopírované tak, jak jsou (kvary se nenasazuje;
 * rss_forum.xml je jednou provždy zmrazený archiv). Malé assety žijí
 * v assets/, do dist i URL jdou bez tohoto prefixu. */
$ASSETY_MALE = ['css', 'js', 'pic', 'fonts'];
$ASSET_FILES = ['favicon.ico', 'favicon.svg', 'favicon-16x16.png', 'favicon-32x32.png',
                'apple-touch-icon.png', 'icon-192.png', 'icon-512.png', 'site.webmanifest',
                'robots.txt', 'rss_forum.xml', '_redirects', '_headers'];

/* Nepublikované podadresáře a soubory (úspora místa) */
$NEPUBLIKOVAT = ['archiv/celost/63/photos', 'archiv/celost/63/thumbnails',
                 'texty/texty.tar', 'texty/matematika/cd.tar.gz'];

$t0 = microtime(true);



function write_file($path, $content)
{
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }
    file_put_contents($path, $content);
}



/** Zachytí výstup includovaného skriptu */
function capture($file)
{
    ob_start();
    include $file;
    return ob_get_clean();
}



/** Vyrenderuje stránku přes index.php se zadaným PATH_INFO */
function render_page($path_info)
{
    $_SERVER['PATH_INFO'] = $path_info;
    return capture(ROOT_DIR . 'src/index.php');
}



/* ---------- 1. stránky ---------- */

exec('rm -rf ' . escapeshellarg($DIST));

$pages = array_keys(data_routes());
$pages[] = 'archiv-novinek';
foreach (data_news() as $item) {
    $pages[] = 'novinka/' . $item['id'];
}
foreach (['student' => 'diskuse', 'ucitel' => 'diskuse-ucitele'] as $who => $base) {
    for ($i = 1; $i <= forum_pocet_stranek($who); $i++) {
        $pages[] = $i === 1 ? $base : "$base/$i";
    }
}
foreach (data_forum() as $root) {
    $pages[] = 'diskuse/vlakno/' . $root['id'];
}

foreach ($pages as $name) {
    $out = $name === '/' ? 'index' : $name;
    write_file($DIST . $out . '.html', render_page(url_for_pathname($name)));
}
write_file($DIST . '404.html', render_page('/tato-stranka-neexistuje'));

/* stránky s id 404 (vlákno, novinka): soubor 404.html by Pages braly
 * jako chybovou stránku celé sekce */
foreach ($pages as $name) {
    if (substr($name, -4) === '/404') {
        write_file($DIST . $name . '/index.html', file_get_contents($DIST . $name . '.html'));
        unlink($DIST . $name . '.html');
    }
}

/* ---------- 2. rss.xml, terms.json ---------- */

write_file($DIST . 'rss.xml', capture(ROOT_DIR . 'src/rss.php'));
foreach (array('' => 'terminy.ics', 'A' => 'terminy-a.ics',
        'BCD' => 'terminy-bcd.ics', 'EF' => 'terminy-ef.ics') as $kat => $ics) {
    $GLOBALS['ICS_KATEGORIE'] = $kat === '' ? null : $kat;
    write_file($DIST . $ics, capture(ROOT_DIR . 'src/ics.php'));
}
unset($GLOBALS['ICS_KATEGORIE']);
write_file($DIST . 'data/terms.json', capture(__DIR__ . '/terms_json.php'));

/* ---------- 3. statické soubory ---------- */

foreach ($ASSETY_MALE as $dir) {
    exec('cp -aT ' . escapeshellarg(ROOT_DIR . 'assets/' . $dir) . ' ' . escapeshellarg($DIST . $dir));
}
/* veškerý publikovaný obsah; -T slévá files/* do dist vedle vyrenderovaných stránek */
exec('cp -aT ' . escapeshellarg(ROOT_DIR . 'files') . ' ' . escapeshellarg($DIST));
foreach ($ASSET_FILES as $file) {
    copy(ROOT_DIR . 'assets/' . $file, $DIST . $file);
}
/* obrázky novinek leží vedle novinek v data/news/ a publikují se odtamtud */
foreach (glob(ROOT_DIR . 'data/news/*.{jpg,png,gif}', GLOB_BRACE) as $obrazek) {
    copy($obrazek, $DIST . 'data/news/' . basename($obrazek));
}
foreach ($NEPUBLIKOVAT as $prefix) {
    exec('rm -rf ' . escapeshellarg($DIST . $prefix));
}

/* ---------- hotovo ---------- */

printf("stránek: %d, za %.1f s\n", count($pages) + 1, microtime(true) - $t0);
