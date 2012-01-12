<?php
define("VALID_ACCESS", 1);

/*echo $_SERVER['PHP_SELF'] . "\r\n";
echo $_SERVER['SERVER_NAME'] . "\r\n";
echo $_SERVER['DOCUMENT_ROOT'] . "\r\n";
echo $_SERVER['SCRIPT_FILENAME'] . "\r\n";
echo $_SERVER['PATH_TRANSLATED'] . "\r\n";
echo $_SERVER['SCRIPT_NAME'] . "\r\n";
echo __FILE__ . "\r\n";*/

require_once('init.php');

/*
 * HTML KOD
 */

echo '<?xml version="1.0" encoding="windows-1250"?>
';

?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'> 
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='cs' lang='cs'>
<head>
<!-- ENCODING /--> 
<meta http-equiv="Content-Type" content="text/html; charset=windows-1250" />
<meta http-equiv='Content-language' content='cs' />
<!-- ENCODING end /-->

<!-- CACHE /--> 
<!-- CACHE - MSIE /--> 
<meta http-equiv='Cache-Control' content='must-revalidate, post-check=0, pre-check=0' /> 
<meta http-equiv='Pragma' content='public' /> 
<!-- CACHE - MSIE end /--> 
<!-- CACHE - other browsers /--> 
<meta http-equiv='Cache-Control' content='no-cache' /> 
<meta http-equiv='Pragma' content='no-cache' /> 
<meta http-equiv='Expires' content='-1' /> 
<!-- CACHE - other browsers end /--> 
<!-- CACHE - end /--> 

<!-- ROBOTS /--> 
<meta name='robots' content='index,follow' /> 
<meta name='googlebot' content='index,follow,snippet,archive' />
<!-- ROBOTS end /--> 

<!-- KEYWORDS & CATEGORIES - but who cares now :-( /--> 
<meta name='description' content='Fyzikální olympiáda, Karlovy Vary, oficiální stránky' /> 
<meta name='keywords' content='fyzika, fyzikální, fyzikalni, olympiada, olympiáda, soutěž, soutez, karlovy vary' /> 
<meta name='category' content='physics' /> 
<!-- KEYWORDS & CATEGORIES - end /--> 

<!-- AUTHOR self promo - use 'crypted' e-mails defeats robotic harvesters /--> 
<meta name='author' content='All: Jan Prachař, e-mail: jan.prachar@fyzikalniolympiada.cz' /> 
<meta name='webmaster' content='All: Jan Prachař, e-mail: webmaster@fyzikalniolympiada.cz' /> 
<meta name='copyright' content='&copy;2005-2005 Jan Prachař, e-mail: jan.prachar@fyzikalniolympiada.cz' /> 
<!-- AUTHOR self promo - end /--> 

<!-- GEOURL /--> 
<meta name='ICBM' content='50.1152, 14.448' /> 
<!-- GEOURL - end /-->
	  
<!-- BROWSER SPECIFIC FEATURES = ALL OFF /--> 
<!-- MSIE - 'helpful' features /--> 
<meta http-equiv='imagetoolbar' content='no' /> 
<meta http-equiv='MSThemeCompatible' content='no' /> 
<meta name='MS.LOCALE' content='cs' /> 
<!-- OPERA - image resizing /--> 
<meta name='autosize' content='off' /> 
<!-- BROWSER SPECIFIC FEATURES = end /-->

<base href="http://<?= SERVER_NAME ?>/" />

<!-- ICON /--> 
<link rel='shortcut icon' type='image/x-icon' href='favicon.ico' /> 
<!-- ICON end /--> 

<!-- NAVIGATION - based on logical relations of documents /--> 
<!-- homepage /--> 
<link rel='home' href='/' /> 
<!-- NAVIGATION - end /--> 

<!-- CASCADING STYLE SHEETS /--> 
<!-- INPAGE STYLE - pagemargin problem solving /--> 
<style type='text/css' media='all'> 
	BODY, HTML {
		border: 0px none; 
		margin: 0px;
		padding: 0px; 
	} 
</style> 
<!-- LINKED STYLE /--> 
<link rel='stylesheet' type='text/css' media='all' href='./css/css_all.css' /> 
<!--<link rel='stylesheet' type='text/css' media='screen' href='./css/css_screen.css' /> -->
<link rel='stylesheet' type='text/css' media='print' href='./css/css_print.css' /> 
<!-- CASCADING STYLE SHEETS - end /--> 

<title> 
Fyzikální olympiáda Karlovy Vary<?php echo ' :: ' . nadpis(); ?>
</title> 
</head>
<body id="fo-cuni-cz/kvary">
<div id="page_title">
	Fyzikální olympiáda &ndash; Karlovy Vary
</div>
<div id="menu">
	<?php echo menu(); ?>
</div>
<div id="left_column">
<div id="submenu">
	<?php echo submenu($GLOBALS['parentID']); ?>
</div>
<div id="buttons">
	<ul>
		<li>
			<table>
			<tr>
				<td colspan="2"><strong>Návštěvy:</strong></td>
			</tr>
			<tr>
				<td>celkem</td>
				<td style="text-align: right"><?php echo $GLOBALS['visits']; ?></td>
			</tr>
			<tr>
				<td>dnes</td>
				<td style="text-align: right"><?php echo $GLOBALS['visits_day']; ?></td>
			</tr>
			<tr>
				<td>online</td>
				<td style="text-align: right"><?php echo $GLOBALS['visits_online']; ?></td>
			</tr>
			</table>
		</li>
	</ul>
</div>
</div>
<div id="title">
	<h1><?php echo nadpis(); ?></h1>
</div>
<div id="content">
				<?php
if ($napln == FILE_NEWS) {
	echo '
				<p>Oficiální stránky karlovarské krajské komise souteže Fyzikální olympiáda &ndash; <a href="http://kvary.fyzikalniolympiada.cz/">kvary.fyzikalniolympiada.cz</a></p>';
	include(ROOT_DIR.FILE_NEWS);
} else {
	include(ROOT_DIR.$napln);
}
	  			?>
</div>
<address>&copy;2005&ndash;<?php echo date('Y') ?> Jan Prachař &ndash; <a href="mailto:webmaster@fyzikalniolympiada.cz" title="Kontaktní email">webmaster@fyzikalniolympiada.cz</a></address>
</body>
</html>
<?php
$mysql->close();
$mysql_odkazy->close();
?>