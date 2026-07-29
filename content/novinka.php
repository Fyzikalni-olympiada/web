<?php
if(!defined("VALID_ACCESS"))	{die("Neoprávněný přístup!");}				//	Ochrana proti neoprávněnému přístupu ke skriptům

$item = $GLOBALS['novinka_id'] === null ? null : data_news_by_id($GLOBALS['novinka_id']);
if ($item === null) {
    /* sem vedou i staré odkazy /novinka?id=N – přesměruje je js/fo.js */
    echo '<p>Novinka nenalezena. <a href="' . ROOT_WWW . '">Všechny novinky</a></p>';
    return;
}

$monthz = array('', 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince');

list($rok, $mesic, $den) = explode('-', $item['date']);
$hourmin = preg_replace('~^0~', '', $item['time']);

echo '
        <div class="post">

            <div class="archive-post-title">
                <h3>' . $item['subject'] . '</h3>
                <p>';
$img = isset($item['image']) ? $item['image'] : null;
if ($img && $img['align'] !== 'block') {
	echo '
	<img src="' . ROOT_WWW . 'upload/' . $img['filename'] . '" style="margin:';
	echo isset($img['vspace']) ? ($img['vspace'] . "px ") : "5px ";
	echo isset($img['hspace']) ? ($img['hspace'] . "px ") : "7px ";
	echo ';float: ' . $img['align'] . '; ' . ($img['align'] == 'left' ? 'margin-left: 0px;' : 'margin-rigth: 0px;') . '" alt="' . $img['alt'] . '" />';
}
echo $item['body'] . '</p>';
if ($img && $img['align'] === 'block') {
	echo '
				<div class="center-box">
					<img src="' . ROOT_WWW . 'upload/' . $img['filename'] . '" class="img-responsive"
					alt="' . $img['alt'] . '" title="' . $img['alt'] . '"/>
				</div>';
}
echo '
				<div class="post-date">
					<span class="link">' . $hourmin . ', ' . (int) $den . '. ' . $monthz[(int) $mesic] . ' ' . $rok . '</span>
					&bull;
					<a href="mailto:' . $item['email'] . '" title="Autor příspěvku" class="sign">' . $item['author'] . '</a>
				</div>
            </div>

            <div class="clearer">&nbsp;</div>

        </div>
        <p><a href="' . ROOT_WWW . '">&#171; Všechny novinky</a></p>';
