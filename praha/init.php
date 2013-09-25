<?php

if(!defined("VALID_ACCESS"))	{die("Neoprávněný přístup!");}				//	Ochrana proti neoprávněnému přístupu ke skriptům

if (function_exists('date_default_timezone_set'))
	date_default_timezone_set('Europe/Prague');

define("TABLE_FILES", "files");
define("TABLE_MENU_STRUCTURE", "menu_structure");
define("TABLE_FORUM", "forum_praha");
define("TABLE_IMAGES", "images");
define("TABLE_NEWS", "news");
define("TABLE_USERS", "users");
define("TABLE_TERMS", "terms");
define("TABLE_COUNTER_ALL", "counter_all");
define("TABLE_COUNTER_ONLINE", "counter_online");
define("TABLE_REFERENTS", "referents");
define("TABLE_PARTICIPANTS", "participants");

define('FILE_INDEX', 'praha/index.php');
define('FILE_NEWS', 'content/news.php');
define('FILE_FORUM', 'content/forum-old.php');

define('FILE_DB_LOG', 'logs/db_error.log');
define('FILE_DB_LOG_TIME', 'logs/db_error_time.txt');

define('SITE', 'praha');

require_once('configure.php');

define('AKTUALNI_ROCNIK', 54);
define('AKTUALNI_ROK', '2012/2013');

/* Pocitadlo */
define('INTERVAL_MEZI_NAVSTEVAMI', 60); //minimalni interval mezi navstevami v minutach
define('INTERVAL_ONLINE', 20); //maximalni doba necinnosti v minutach, kdy je clovek povazovan online

/* Novinky */
define('NOVINKY_WHERES', 'praha');
define('NOVINKY_NA_STRANKU', 6);
define('NOVINKY_INTERVAL', 90); //maximalni stari zobrazene novinky ve dnech

/* Menu */
define('MENU_ODDELOVAC', ' ');
define('SUBMENU_ODRAZKA', '');

/* Adresare */
define('DIR_VYSLEDKY', 'praha/vysledky/');

/* Log */
define('ERROR_MAIL_INTERVAL', 1); //interval posilani chybovych mailu in days


/* GLOBALNI PROMENNE 
 *
 * $kdo
 * $parentID
 * $structureID
 * $nadpis
 * $napln
*/
 
// Defaultní hodnoty, pokud je prázdné GET 
$GLOBALS['who'] = $GLOBALS['kdo'] = 'student';
$GLOBALS['parentID'] = 72;
$GLOBALS['structureID'] = 76;
$GLOBALS['napln'] = FILE_NEWS;
$GLOBALS['nadpis'] = 'Novinky';


/*
 * KONEC GLOBALNICH PROMENNYCH
 */

require_once(ROOT_DIR.'classes/db.php');
require_once(ROOT_DIR.'functions/folib.php');

/*
 * SPOJENÍ S DATABÁZÍ
 */

$mysql_odkazy = new db("r", 0);
$mysql = new db("w", 0);


/*
 * PARSOVÁNÍ DOTAZU
 */

/* urcime napln, nadpis, parentID, structureID */
parsuj();

if (isset($_GET['who']) && in_array($_GET['who'], array('student','ucitel','organizator')) ) {
	$GLOBALS['who'] = $GLOBALS['kdo'] = $_GET['who'];
}
