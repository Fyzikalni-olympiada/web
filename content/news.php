<?php
if(!defined("VALID_ACCESS"))	{die("Neoprávněný přístup!");}				//	Ochrana proti neoprávněnému přístupu ke skriptům

$nastranku = NOVINKY_NA_STRANKU;
$interval = NOVINKY_INTERVAL;
$where = NOVINKY_WHERES;

if ( isset($_GET["page"]) )
  $strana = $_GET["page"];
else
  $strana = 1;

$monthz = Array('', 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince');
$monthz_short = Array('', 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'červen.', 'srpna', 'září', 'října', 'listop.', 'prosin.');
$rok = date("Y");
$mesic = date("m");

if ( $strana == 1 )
	$mysql->query('
  		SELECT id, YEAR(date) AS year, MONTH(date) AS month, DAYOFMONTH(date) AS day, TIME_FORMAT(time, "%k:%i") AS hourmin, date, time, subject, text, pic_id, name, email
  		FROM news
		WHERE DATE_SUB(CURDATE(),INTERVAL ' . $interval . ' DAY) <= date
		AND wheres = \'' . $where . '\'
		AND who LIKE \'%' . $who . '%\'
		ORDER BY date DESC, time DESC
		LIMIT 0, ' . $nastranku . '
	');
else {
	$mysql->query('
		SELECT COUNT(*) AS count
		FROM news
		WHERE DATE_SUB(CURDATE(),INTERVAL ' . $interval . ' DAY) <= date
		AND wheres = \'' . $where . '\'
		AND who LIKE \'%' . $who . '%\'
	');
	if (!($row1 = $mysql->fetch_array()))
		$row1['count'] = 0;
	
	$mysql->query("
		SELECT COUNT(*) AS count
		FROM news
		WHERE wheres = '" . $where . "'
		AND who
		LIKE '%" . $who . "%'
	");
	if (!($row2 = $mysql->fetch_array()))
		$row2['count'] = 0;

	$mysql->query('
		SELECT id, YEAR(date) AS year, MONTH(date) AS month, DAYOFMONTH(date) AS day, TIME_FORMAT(time, "%k:%i") AS hourmin, date, time, subject, text, pic_id, name, email
		FROM news
		WHERE wheres = \'' . $where . '\'
		AND who LIKE \'%' . $who . '%\'
		ORDER BY date DESC, time DESC
		LIMIT ' . min($row1["count"],$nastranku) . ', ' . ($row2["count"] - min($row1["count"],$nastranku) )
	);
}
$result = $mysql->vysledek;
$i = 0;

while ( $row = mysql_fetch_array($result, MYSQL_ASSOC) ) {
    if ($i>0) {
        echo '
        <div class="archive-separator"></div>';
    }
    
    echo '
        <div class="post">

            <!--div class="archive-post-date">
                <div class="archive-post-day">' . $row["day"] . '</div>
                <div class="archive-post-month">' . $monthz_short[$row["month"]] . '</div>
            </div-->

            <div class="archive-post-title"><a name="'.++$i.'"></a>
                <h3>' . $row["subject"] . '</h3>
                <p>';
	if ($row["pic_id"] != NULL) {       //i obrázek
    	$mysql->query('
    		SELECT filename, align, hspace, vspace, alt
    		FROM images
    		WHERE id=' . $row["pic_id"]
		);
		if ( $row1 = $mysql->fetch_array() ) {
			if ($row1['align'] !== 'block') {
				echo '
					<img src="' . ROOT_WWW . 'upload/' . $row1["filename"] . '" style="margin:';
				echo $row1["vspace"] != NULL ? ($row1["vspace"] . "px ") : "5px ";
				echo $row1["hspace"] != NULL ? ($row1["hspace"] . "px ") : "7px ";
				echo ';float: '  . $row1["align"] . '; '.($row1['align'] == 'left' ? 'margin-left: 0px;' : 'margin-rigth: 0px;').'" alt="'  . $row1["alt"] . '" title="'  . $row1["alt"] . '"/>';
			}
		}
	}
	eval("\$row[\"text\"] = \"$row[text]\";");
    echo $row["text"] . '</p>';
	if ($row['pic_id'] != NULL && $row1 && $row1['align'] === 'block') {
		echo '
				<div class="center-box">
					<img src="' . ROOT_WWW . 'upload/' . $row1["filename"] . '" class="img-responsive"
					alt="'  . $row1["alt"] . '" title="'  . $row1["alt"] . '"/>
				</div>';
	}
	echo '
				<div class="post-date">
					<a href="' . odkaz2('content/novinka.php', array('id'=>$row['id'], 'page' => NULL)) . '" title="Trvalý odkaz na příspěvek" class="link">' . $row["hourmin"] . ', ' . $row["day"] . '. ' . $monthz[$row["month"]] . ' ' . $row["year"] . '</a>
                    <span title="id=' . $row['id'] . '">&bull;</span>
                    <a href="mailto:' . $row['email'] . '" title="Autor příspěvku" class="sign">' . $row['name'] . '</a>
				</div>
            </div>

            <div class="clearer">&nbsp;</div>

        </div>';
}

echo '
    <div class="archive-pagination archive-pagination-bottom">';

if ($strana == 1) {
    echo '<div class="right"><a href="' . odkaz2("content/news.php", array('page'=>2)) . '">Starší novinky &#187;</a></div>';
} else {
    echo '<div class="left"><a href="' . odkaz2("content/news.php", array('page'=>1)) . '">&#171; Novinky</a></div>';
}

echo '
        <div class="clearer">&nbsp;</div>

    </div>
';


?>
