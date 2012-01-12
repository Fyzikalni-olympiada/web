<?php
define("VALID_ACCESS", 1);
define("INTERVAL_RSS_FORUM", 30);
require_once('init.php');

$vysledek = $mysql->query('
	SELECT *
	FROM ' . VIEW_FORUM . ' forum
	WHERE lang="cz"
	AND DATE_SUB(NOW(),INTERVAL '.INTERVAL_RSS_FORUM.' DAY) <= posted_timestamp
	ORDER BY posted_timestamp DESC
');

$strRSS = '<?xml version=\'1.0\' encoding=\'windows-1250\'?>
<rss version=\'2.0\'>
	<channel>
		<title>Fyzikální olympiáda</title>
		<link>http://fyzikalniolympiada.cz/?file=19</link>
		<description>Fyzikální olympiáda, diskuse.</description>
		<language>cs</language>
		<copyright>Copyright 2004-' . date('Y') . ', FO</copyright>
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
		
		


while ($radek = mysql_fetch_array($vysledek)) {
	if (empty($radek["email"])) {
		$radek["email"] = "webmaster@fyzikalniolympiada.cz";
	} else {
		$radek["email"] = str_replace("@", "-z@vinac-", $radek["email"]);
	}
	$strRSS .= '
		<item>
			<title>' . decode($radek['title']) . '</title>
			<link>http://' . SERVER_NAME . odkaz2('content/forum.php', array('forum_id'=>$radek['id'],'who'=>$radek['who'],'sort'=>'vlakno')) . '</link>
			<description>' . decode($radek['text']) . '</description>
			<author>' . $radek['email'] . ' (' . decode($radek['name']) . ')</author>';
	$strRSS .= '
			<comments>http://' . SERVER_NAME . odkaz('content/forum.php') . '</comments>
			<pubDate>' . date('r', $radek['unix_time']) . '</pubDate>
			<guid>http://' . SERVER_NAME . odkaz2('content/forum.php', array('forum_id'=>$radek['id'],'who'=>$radek['who'],'sort'=>'vlakno')) . '</guid>
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

function decode($text)
{
	$text = str_replace('&nbsp;', ' ', $text);
	$text = str_replace('&ndash;', '-', $text);
	$text = str_replace('<br />', "\n", $text);
	$text = strip_tags($text);
	$text = str_replace('&', '&#x26;', $text);
	$text = str_replace('<', '&#x3C;', $text);
	$text = str_replace('>', '&#x3E;', $text);
	$text = str_replace('&#x26;#', '&#', $text); /* Ciselne entity prekodovat zpet */
	return $text;
}

?>