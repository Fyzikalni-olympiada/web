<?php
if ($route['news_archiv']) {
	echo '
<h2>Archiv novinek</h2>';
} else {
	include ROOT_DIR . 'html/zapojeni.html';
	echo '
<hr class="aktuality-predel">';
}

$novinky_list = array_values(array_filter(data_news(), function ($item) use ($route) {
	return empty($item['homepage']) === $route['news_archiv'];
}));

$i = 0;
foreach ($novinky_list as $item) {
	if ($i > 0) {
		echo '
        <div class="archive-separator"></div>';
	}
	echo novinka_html($item, true, ++$i);
}

echo '
    <div class="archive-pagination archive-pagination-bottom">';

if ($route['news_archiv']) {
	echo '<div class="left"><a href="/">&#171; Novinky</a></div>';
} else {
	echo '<div class="right"><a href="/archiv-novinek">Starší novinky &#187;</a></div>';
}

echo '
        <div class="clearer">&nbsp;</div>

    </div>
';
