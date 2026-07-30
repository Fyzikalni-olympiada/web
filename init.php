<?php

if(!defined("VALID_ACCESS"))	{die("Neoprávněný přístup!");}				//	Ochrana proti neoprávněnému přístupu ke skriptům

date_default_timezone_set('Europe/Prague');

define('ROOT_DIR', __DIR__ . '/');

/* Kanonická adresa webu (build); při lokálním náhledu se odvodí z requestu */
if (PHP_SAPI === 'cli' || getenv('FO_BUILD')) {
	define('BASE_URL', 'https://fyzikalniolympiada.cz/');
} else {
	define('BASE_URL', 'http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . '/');
}

define('FILE_INDEX', 'index.php');
define('FILE_NEWS', 'content/news.php');
define('FILE_FORUM', 'content/forum.php');

require_once(ROOT_DIR.'config.php');

/* Diskuse (zmrazený archiv) */
define('FORUM_VLAKEN_NA_STRANKU', 7);

/* Menu */
define('MENU_ODDELOVAC', '');
define('SUBMENU_ODRAZKA', '');

/* GLOBALNI PROMENNE
 *
 * $parentID
 * $structureID
 * $nadpis
 * $napln
*/

$GLOBALS['who'] = 'student';
$GLOBALS['kdo'] = 'student';

$GLOBALS['get'] = $_GET;

require_once(ROOT_DIR.'functions/data.php');
require_once(ROOT_DIR.'functions/folib.php');
