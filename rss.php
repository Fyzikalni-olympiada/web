<?php
include_once('init.php');

$base = rtrim(BASE_URL, '/');

$strRSS = '<?xml version=\'1.0\' encoding=\'utf-8\'?>
<rss version=\'2.0\'>
	<channel>
		<title>Fyzikální olympiáda</title>
		<link>' . $base . '</link>
		<description>Fyzikální olympiáda, aktuality.</description>
		<language>cs</language>
		<copyright>Copyright 2004-' . date('Y') . ', Fyzikální olympiáda</copyright>
		<webMaster>webmaster@fyzikalniolympiada.cz (Prachař, Jan)</webMaster>
		<pubDate>' . date('r') . '</pubDate>
		<docs>http://backend.userland.com/rss2/</docs>
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

function decode($text)
{
	$text = str_replace('&nbsp;', ' ', $text);
	$text = str_replace('&ndash;', '-', $text);
	return htmlspecialchars(strip_tags($text), ENT_XML1);
}
