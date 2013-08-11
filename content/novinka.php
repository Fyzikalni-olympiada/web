<?php
if(!defined("VALID_ACCESS"))	{die("Neoprávněný přístup!");}				//	Ochrana proti neoprávněnému přístupu ke skriptům

if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    die('<p>Chybný požadavek.</p>');
}

$monthz = array('', 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince');
$monthz_short = array('', 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'červen.', 'srpna', 'září', 'října', 'listop.', 'prosin.');
$rok = date("Y");
$mesic = date("m");

$mysql->query('
    SELECT YEAR(date) AS year, MONTH(date) AS month, DAYOFMONTH(date) AS day, TIME_FORMAT(time, "%k:%i") AS hourmin, date, time, subject, text, pic_id, name, email
    FROM news
    WHERE id="' . $id . '"
');

if ( $row = $mysql->fetch_array() ) {
    echo '
        <div class="post">

            <div class="archive-post-date">
                <div class="archive-post-day">' . $row["day"] . '</div>
                <div class="archive-post-month">' . $monthz_short[$row["month"]] . '</div>
            </div>

            <div class="archive-post-title">
                <h3>' . $row["subject"] . '</h3>
                <p>';
	if ($row["pic_id"] != NULL) {       //i obrázek
    	$mysql->query('
    		SELECT filename, align, hspace, vspace, alt
    		FROM images
    		WHERE id=' . $row["pic_id"]
		);
		if ( $row1 = $mysql->fetch_array() ) {
			echo '
	<img src="' . ROOT_WWW . 'upload/' . $row1["filename"] . '" style="margin:';
			echo $row1["vspace"] != NULL ? ($row1["vspace"] . "px ") : "5px ";
			echo $row1["hspace"] != NULL ? ($row1["hspace"] . "px ") : "7px ";
			echo ';float: '  . $row1["align"] . '; '.($row1['align'] == 'left' ? 'margin-left: 0px;' : 'margin-rigth: 0px;').'" alt="'  . $row1["alt"] . '" />';
		}
	}
	eval("\$row[\"text\"] = \"$row[text]\";");
    echo $row["text"] . '</p>
                <div class="quiet">
                    <a href="mailto:' . $row['email'] . '" title="Autor příspěvku" class="sign">' . $row['name'] . '</a>
                </div>
                <div class="post-date">' . $row["hourmin"] . ', ' . $row["day"] . '. ' . $monthz[$row["month"]] . ' ' . $row["year"] . '</div>
            </div>

            <div class="clearer">&nbsp;</div>

        </div>';

} else {
    echo '<p>Novinka nenalezena</p>';
}
