<?php
define("VALID_ACCESS", 1);
define("INTERVAL", "150");
include_once('init.php');

$mysql->query("
	SELECT id, name, email, UNIX_TIMESTAMP(CONCAT(date, ' ' , time)) AS unix_time, subject, text
	FROM " . TABLE_NEWS . "
	WHERE wheres='" . NOVINKY_WHERES . "'
	AND who LIKE '%" . $GLOBALS['kdo'] . "%'
	AND DATE_SUB(CURDATE(),INTERVAL " . INTERVAL . " DAY) <= date
	ORDER BY date DESC, time DESC
");

$strRSS = '<?xml version=\'1.0\' encoding=\'windows-1250\'?>
<rss version=\'2.0\'>
	<channel>
		<title>Fyzikální olympiáda</title>
		<link>http://fyzikalniolympiada.cz</link>
		<description>Fyzikální olympiáda, aktuality.</description>
		<language>cs</language>
		<copyright>Copyright 2004-2005, Jan Prachaø</copyright>
		<managingEditor>jan.prachar@fyzikalniolympiada.cz (Prachaø, Jan)</managingEditor>
		<webMaster>webmaster@fyzikalniolympiada.cz (Prachaø, Jan)</webMaster>
		<pubDate>' . date('r') . '</pubDate>
		<docs>http://backend.userland.com/rss2/</docs>
		<image>
			<url>http://fyzikalniolympiada.cz/pic/fo_logo.gif</url>
			<title>Fyzikální olympiáda - oficiální stránky</title>
			<link>http://fyzikalniolympiada.cz</link>
			<width>62</width>
			<height>83</height>
			<description>Fyzikální olympiáda - oficiální stránky støedoškolské soutìže</description>
		</image>';

while ($row = $mysql->fetch_array()) {
	eval("\$row[\"text\"] = \"$row[text]\";");
	$strRSS .= '
		<item>
			<title>' . decode($row['subject']) . '</title>
			<link>http://fyzikalniolympiada.cz/novinka?id=' . $row['id'] . '&amp;who=' . $GLOBALS['kdo'] . '</link>
			<description>' . decode($row['text']) . '</description>
			<author>' . $row['email'] . ' (' . $row['name'] . ')</author>';
	$strRSS .= '
			<comments>' . odkaz('diskuse/forum.php') . '</comments>
			<guid>http://fyzikalniolympiada.cz/novinka?id=' . $row['id'] . '&amp;who=' . $GLOBALS['kdo'] . '</guid>
			<pubDate>' . date('r', $row['unix_time']) . '</pubDate>
		</item>';
}

$strRSS .= '
	</channel>
</rss>';

//header("Content-type: application/rss+xml; charset=windows-1250"); 
Header("Content-type: text/xml; charset=windows-1250");
Header("Pragma: no-cache");
Header("Cache-Control: no-cache");
Header("Expires: ".GMDate("D, d M Y H:i:s")." GMT");
echo $strRSS;

$mysql->close();
$mysql_odkazy->close();

function decode($text)
{
	$text = str_replace('&nbsp;', ' ', $text);
	$text = str_replace('&ndash;', '-', $text);
	return strip_tags($text);
}
