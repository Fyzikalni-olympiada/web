<?php

if(!defined("VALID_ACCESS"))	{die("Neoprávnìný pøístup!");}				//	Ochrana proti neoprávnìnému pøístupu ke skriptùm

if (function_exists('date_default_timezone_set'))
	date_default_timezone_set('Europe/Prague');

define("TABLE_FILES", "files");
define("TABLE_MENU_STRUCTURE", "menu_structure");
define("TABLE_FORUM", "forum");
define("TABLE_CONSTANTS", "constants");
define("TABLE_IMAGES", "images");
define("TABLE_NEWS", "news");
define("TABLE_USERS", "users");
define("TABLE_TERMS", "terms");
define("TABLE_COUNTER_ALL", "counter_all");
define("TABLE_COUNTER_ONLINE", "counter_online");
define("VIEW_FORUM", "V_forum");

define('FILE_INDEX', 'index.php');
define('FILE_NEWS', 'content/news.php');
define('FILE_FORUM', 'content/forum.php');

define('FILE_DB_LOG', 'logs/db_error.log');
define('FILE_DB_LOG_TIME', 'logs/db_error_time.txt');

define('SITE', '');

require_once('configure.php');

define('AKTUALNI_ROCNIK', 54);
define('AKTUALNI_ROK', '2012/2013');

/* Pocitadlo */
define('INTERVAL_MEZI_NAVSTEVAMI', 60); //minimalni interval mezi navstevami v minutach
define('INTERVAL_ONLINE', 20); //maximalni doba necinnosti v minutach, kdy je clovek povazovan online

/* Novinky */
define('NOVINKY_WHERES', 'celost');
define('NOVINKY_NA_STRANKU', 6);
define('NOVINKY_INTERVAL', 90); //maximalni stari zobrazene novinky ve dnech

define('GUEST_TEST_TEXT', '2'); //kontrolni text pro prispevatele ve foru


/* Menu */
define('MENU_ODDELOVAC', '');
define('SUBMENU_ODRAZKA', '');

/* Log */
define('ERROR_MAIL_INTERVAL', 1); //interval posilani chybovych mailu in days

/* GLOBALNI PROMENNE 
 *
 * $who
 * $difference
 * $parentID
 * $structureID
 * $nadpis
 * $napln
*/
 
// Defaultní hodnoty, pokud je prázdné GET 
$GLOBALS['who'] = 'student';
$GLOBALS['difference'] = 1;
$GLOBALS['parentID'] = 1;
$GLOBALS['structureID'] = 7;
$GLOBALS['napln'] = FILE_NEWS;
$GLOBALS['nadpis'] = 'Novinky';

$GLOBALS['get'] = $_GET;

/*
 * KONEC GLOBALNICH PROMENNYCH
 */

require_once(ROOT_DIR.'classes/db.php');
require_once(ROOT_DIR.'functions/folib.php');

/*
 * SPOJENÍ S DATABÁZÍ
 */

$mysql_odkazy = new db("r",0);
$mysql = new db("w",0);


/*
 * PARSOVÁNÍ DOTAZU
 */

/* urcime napln, nadpis, parentID, structureID */
parsuj();
$GLOBALS['get'] = $_GET;

if (isset($_GET['who']) && in_array($_GET['who'], array('student','ucitel','organizator')) ) {
	$GLOBALS['who'] = $_GET['who'];
}
$GLOBALS['kdo'] = $GLOBALS['who'];
	
/*
 * POÈÍTADLO
 */
 
list($GLOBALS['visits'], $GLOBALS['visits_day'], $GLOBALS['visits_online']) = pocitadlo();

