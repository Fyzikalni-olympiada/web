<?php
/**
 * Vyrenderuje jeden obsahový HTML fragment obsahující PHP (odkaz() apod.)
 * do stdout. Volá ho build.php v podprocesu, aby se fragmenty navzájem
 * neovlivňovaly (deklarace funkcí, globální proměnné).
 *
 * Použití: php build/render_fragment.php <soubor>
 */

require dirname(__DIR__) . '/src/init.php';

if (empty($argv[1]) || !is_file($argv[1])) {
    fwrite(STDERR, "chybí soubor\n");
    exit(1);
}

include $argv[1];
