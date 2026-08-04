<?php
/**
 * Sestaví statický web do dist/.
 *
 * Použití: php build/build.php
 *
 * - stránky z data/files.yaml -> dist/<pathname>.html (hosting servíruje bez přípony)
 * - novinky (/novinka/<id>, /archiv-novinek), diskuse (seznamy + vlákna), 404
 * - rss.xml, data/terms.json
 * - kopie statických adresářů; fragmenty s PHP se předrenderují (render_fragment.php)
 */

require dirname(__DIR__) . '/init.php';

$DIST = ROOT_DIR . 'dist/';

/* Adresáře a soubory kopírované tak, jak jsou (kvary se nenasazuje;
 * rss_forum.xml je jednou provždy zmrazený archiv) */
$ASSET_DIRS = ['css', 'js', 'pic', 'images', 'fonts', 'dokumenty',
               'archiv', 'texty', 'vysledky', 'tana', 'upload'];
$ASSET_FILES = ['favicon.ico', 'favicon.svg', 'favicon-16x16.png', 'favicon-32x32.png',
                'apple-touch-icon.png', 'icon-192.png', 'icon-512.png', 'site.webmanifest',
                'robots.txt', 'rss_forum.xml', '_redirects', '_headers'];

/* Nepublikované podadresáře a soubory (úspora místa) */
$NEPUBLIKOVAT = ['archiv/celost/63/photos', 'archiv/celost/63/thumbnails',
                 'texty/texty.tar', 'texty/matematika/cd.tar.gz'];

$t0 = microtime(true);
$selhani = 0;



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
    return capture(ROOT_DIR . 'index.php');
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

write_file($DIST . 'rss.xml', capture(ROOT_DIR . 'rss.php'));
write_file($DIST . 'terminy.ics', capture(ROOT_DIR . 'ics.php'));
write_file($DIST . 'data/terms.json', capture(__DIR__ . '/terms_json.php'));

/* ---------- 3. statické soubory ---------- */

foreach ($ASSET_DIRS as $dir) {
    /* -T: slij do cílového adresáře i když už existuje (vyrenderované stránky) */
    exec('cp -aT ' . escapeshellarg(ROOT_DIR . $dir) . ' ' . escapeshellarg($DIST . $dir));
}
foreach ($ASSET_FILES as $file) {
    copy(ROOT_DIR . $file, $DIST . $file);
}
foreach ($NEPUBLIKOVAT as $prefix) {
    exec('rm -rf ' . escapeshellarg($DIST . $prefix));
}

/* ---------- 4. fragmenty s PHP se předrenderují ---------- */

/* Jen <?php / <?= (bez short_open_tag, aby <?xml zůstalo netknuté).
 * Podproces kvůli izolaci: některé fragmenty deklarují stejné funkce. */
$fragments = [];
exec('grep -rlE "<\?(php|=)" ' . implode(' ', array_map(function ($d) {
    return escapeshellarg(ROOT_DIR . $d);
}, $ASSET_DIRS)) . ' --include="*.html"', $fragments);
foreach ($fragments as $src) {
    exec(PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/render_fragment.php') . ' '
        . escapeshellarg($src), $out, $rc);
    if ($rc === 0) {
        file_put_contents($DIST . substr($src, strlen(ROOT_DIR)), implode("\n", $out));
    } else {
        fwrite(STDERR, "! fragment selhal: $src\n");
        $selhani++;
    }
    $out = [];
}

/* ---------- hotovo ---------- */

printf("stránek: %d, fragmentů: %d, za %.1f s\n",
    count($pages) + 1, count($fragments), microtime(true) - $t0);

if ($selhani > 0) {
    fwrite(STDERR, "build selhal: $selhani chyb\n");
    exit(1);
}
