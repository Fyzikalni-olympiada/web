<?php
if(!defined("VALID_ACCESS"))	{die("Neoprávněný přístup!");}				//	Ochrana proti neoprávněnému přístupu ke skriptům

$novinky_list = array_values(array_filter(data_news(), function ($item) use ($route) {
	return empty($item['homepage']) === $route['news_archiv'];
}));

$i = 0;
foreach ($novinky_list as $item) {
	if ($i > 0) {
		echo '
        <div class="archive-separator"></div>';
	}

	list($rok, $mesic, $den) = explode('-', $item['date']);
	$hourmin = preg_replace('~^0~', '', $item['time']);

	echo '
        <div class="post">

            <div class="archive-post-title"><a name="' . ++$i . '"></a>
                <h3>' . $item['subject'] . '</h3>
                <p>';
	$img = isset($item['image']) ? $item['image'] : null;
	if ($img && $img['align'] !== 'block') {
		echo '
					<img src="/' . 'upload/' . $img['filename'] . '" style="margin:';
		echo isset($img['vspace']) ? ($img['vspace'] . "px ") : "5px ";
		echo isset($img['hspace']) ? ($img['hspace'] . "px ") : "7px ";
		echo ';float: ' . $img['align'] . '; ' . ($img['align'] == 'left' ? 'margin-left: 0px;' : 'margin-rigth: 0px;') . '" alt="' . $img['alt'] . '" title="' . $img['alt'] . '"/>';
	}
	echo $item['body'] . '</p>';
	if ($img && $img['align'] === 'block') {
		echo '
				<div class="center-box">
					<img src="/' . 'upload/' . $img['filename'] . '" class="img-responsive"
					alt="' . $img['alt'] . '" title="' . $img['alt'] . '"/>
				</div>';
	}
	echo '
				<div class="post-date">
					<a href="/' . 'novinka/' . $item['id'] . '" title="Trvalý odkaz na příspěvek" class="link">' . $hourmin . ', ' . (int) $den . '. ' . MESICE[(int) $mesic] . ' ' . $rok . '</a>
                    <span title="id=' . $item['id'] . '">&bull;</span>
                    <a href="mailto:' . $item['email'] . '" title="Autor příspěvku" class="sign">' . $item['author'] . '</a>
				</div>
            </div>

            <div class="clearer">&nbsp;</div>

        </div>';
}

echo '
    <div class="archive-pagination archive-pagination-bottom">';

if ($route['news_archiv']) {
	echo '<div class="left"><a href="/' . '">&#171; Novinky</a></div>';
} else {
	echo '<div class="right"><a href="/' . 'archiv-novinek">Starší novinky &#187;</a></div>';
}

echo '
        <div class="clearer">&nbsp;</div>

    </div>
';
