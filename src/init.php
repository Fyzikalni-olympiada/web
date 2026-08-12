<?php

date_default_timezone_set('Europe/Prague');

define('ROOT_DIR', dirname(__DIR__) . '/');

/* Kanonická adresa webu (build); při lokálním náhledu se odvodí z requestu */
if (PHP_SAPI === 'cli') {
	define('BASE_URL', 'https://fyzikalniolympiada.cz/');
} else {
	define('BASE_URL', 'http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . '/');
}

define('FILE_NEWS', 'src/content/news.php');
define('FILE_FORUM', 'src/content/forum.php');

require_once(__DIR__ . '/config.php');

/* Diskuse (zmrazený archiv) */
define('FORUM_VLAKEN_NA_STRANKU', 7);

require_once(__DIR__ . '/functions/data.php');
require_once(__DIR__ . '/functions/folib.php');
require_once(__DIR__ . '/functions/forum.php');
