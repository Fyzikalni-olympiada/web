<?php
include_once('init.php');

$base = rtrim(BASE_URL, '/');

$strRSS = '<?xml version=\'1.0\' encoding=\'utf-8\'?>
<rss version=\'2.0\' xmlns:atom=\'http://www.w3.org/2005/Atom\'>
	<channel>
		<title>Fyzikální olympiáda</title>
		<link>' . $base . '</link>
		<atom:link href=\'' . $base . '/rss.xml\' rel=\'self\' type=\'application/rss+xml\'/>
		<description>Fyzikální olympiáda, aktuality.</description>
		<language>cs</language>
		<copyright>Copyright 2004-' . date('Y') . ', Fyzikální olympiáda</copyright>
		<webMaster>webmaster@fyzikalniolympiada.cz (Prachař, Jan)</webMaster>
		<lastBuildDate>' . date('r') . '</lastBuildDate>
		<docs>https://www.rssboard.org/rss-specification</docs>
		<image>
			<url>' . BASE_URL . 'pic/fo_logo.gif</url>
			<title>Fyzikální olympiáda - oficiální stránky</title>
			<link>' . $base . '</link>
			<width>62</width>
			<height>83</height>
			<description>Fyzikální olympiáda - oficiální stránky středoškolské soutěže</description>
		</image>';

foreach (array_slice(data_news(), 0, 15) as $item) {
	$url = BASE_URL . 'novinka/' . $item['id'];
	$strRSS .= '
		<item>
			<title>' . decode($item['subject']) . '</title>
			<link>' . $url . '</link>
			<description>' . decode($item['body']) . '</description>
			<author>' . $item['email'] . ' (' . $item['author'] . ')</author>
			<guid>' . $url . '</guid>
			<pubDate>' . date('r', strtotime($item['date'] . ' ' . $item['time'])) . '</pubDate>
		</item>';
}

$strRSS .= '
	</channel>
</rss>';

Header("Content-type: text/xml; charset=utf-8");
echo $strRSS;

/** HTML novinky -> prostý text bezpečný pro XML */
function decode($text)
{
	$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$text = str_replace("\u{a0}", ' ', $text);
	return htmlspecialchars(strip_tags($text), ENT_XML1);
}
