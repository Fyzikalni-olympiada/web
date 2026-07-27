<?php
if(!defined("VALID_ACCESS"))	{
	die("Neoprávněný přístup!");
}
//	Ochrana proti neoprávněnému přístupu ke skriptům

if (TABLE_FORUM != 'forum') {
	header('Location: '.odkaz('content/forum-old.php', null, 0));
	die();
}

include_once(ROOT_DIR.'functions/forum.php');

/** Načtení strany */
if (!isset($GLOBALS['get']['page']) || empty($GLOBALS['get']['page']) || intval($GLOBALS['get']['page']) < 1) {
	$GLOBALS['get']['page'] = 1;
	$strana = 1;
} else {
	$strana = intval($GLOBALS['get']['page']);
}


/** Způsob třídění */
if (isset($GLOBALS['get']['sort']) && ($GLOBALS['get']['sort'] == 'chronologicky')) {
	$sort = 'chronologicky';
} elseif (isset($GLOBALS['get']['sort']) && ($GLOBALS['get']['sort'] == 'vlakno') && isset($GLOBALS['get']['forum_id']) && is_numeric($GLOBALS['get']['forum_id'])) {
	$sort = 'vlakno';
} else {
	$sort = 'vlakna';
}

if (!isset($GLOBALS['get']['news_id']) || empty($GLOBALS['get']['news_id'])) {
	$GLOBALS['get']['news_id'] = null;
	$news_id = null;
} else {
	/** Kontrola news_id */
	$GLOBALS['mysql']->query('
		SELECT id 
		FROM ' . TABLE_NEWS . '
		WHERE id=' . db::escape_string($GLOBALS['get']['news_id']) . '
	');
	if ($row = $GLOBALS['mysql']->fetch_array()) {
		$news_id = $row['id'];
		$GLOBALS['get']['news_id'] = $row['id'];
	} else {
		$news_id = null;
		$GLOBALS['get']['news_id'] = null;
	}
}

if ($kdo == "student")
	echo '
	<h2>Diskusní fórum pro studenty</h2>';
if ($kdo == "ucitel")
	echo '
	<h2>Diskusní fórum pro učitele</h2>';

echo '
<p><strong>' . lng('Diskusní fórum je uzavřeno a slouží už jen jako archiv starších příspěvků.','The discussion forum is closed and serves only as an archive of older posts.') . '</strong>
' . lng('Máte-li dotaz nebo připomínku, napište nám na','If you have a question or a comment, please e-mail us at') . ' <a href="mailto:info@fyzikalniolympiada.cz">info@fyzikalniolympiada.cz</a>.</p>';


echo '
<p>
<strong>' . lng('Seřadit:','Sort by') . '</strong> ';

if ($sort == 'chronologicky') {
	echo '<a href="' . odkaz2(null, array('sort'=>'vlakna')) . '" title="' . lng('Seřadit podle vláken','Sort by threads') . '">' . lng('podle vláken','threads') . '</a>';
} elseif ($sort == 'vlakna') {
	echo '<a href="' . odkaz2(null, array('sort'=>'chronologicky')) . '" title="' . lng('Seřadit chronologicky','Sort by date') . '">' . lng('chronologicky','date') . '</a>';
} else {
	echo '<a href="' . odkaz2(null, array('sort'=>'vlakna','forum_id'=>null)) . '" title="' . lng('Seřadit podle vláken','Sort by threads') . '">' . lng('podle vláken','threads') . '</a>';
	echo '&nbsp;|&nbsp;';
	echo '<a href="' . odkaz2(null, array('sort'=>'chronologicky','forum_id'=>null)) . '" title="' . lng('Seřadit chronologicky','Sort by date') . '">' . lng('chronologicky','date') . '</a>';
}

echo '
</p>
';

echo '

<div id="comments">
    <div class="comment-list-wrapper">
';

if ($sort == 'chronologicky') {
        $mysql->query('
                SELECT CEIL(COUNT(id)/constants.value) AS pocet_stranek
                FROM ' . TABLE_FORUM . ', ' . TABLE_CONSTANTS . ' constants
                WHERE lang="' . lng() . '"
				AND who='. db::escape_string($GLOBALS['kdo']).'
        		AND constants.name="POCET_PRISPEVKU_NA_STRANKU_CHRON"
        		AND news_id'.db::escape_string_where($GLOBALS['news_id']).'
        ');
        $row = $mysql->fetch_array();
        $pocet_stranek = $row["pocet_stranek"];
} elseif ($sort == 'vlakno') {
        $pocet_stranek = 1;
} else { //$sort == 'vlakna'
        $mysql->query('
                SELECT CEIL(COUNT(id)/constants.value) AS pocet_stranek
                FROM ' . TABLE_FORUM . ', ' . TABLE_CONSTANTS . ' constants
                WHERE reakcena_id=0
                AND lang="' . lng() . '"
				AND who='. db::escape_string($GLOBALS['kdo']).'
        		AND constants.name="POCET_PRISPEVKU_NA_STRANKU"
        		AND news_id'.db::escape_string_where($GLOBALS['news_id']).'
        ');
        $row = $mysql->fetch_array();
        $pocet_stranek = $row["pocet_stranek"];
}
if ($sort == 'chronologicky') {
        echo vypis_forum_chronologicky($strana);
} elseif ($sort == 'vlakno') {
        $row['reakcena_id'] = $GLOBALS['get']['forum_id'];
        do { //hledame id korenoveho prispevku
            $root_id = $row['reakcena_id'];
            $query = '
                    SELECT reakcena_id
                    FROM ' . TABLE_FORUM . '
                    WHERE id=' . $GLOBALS['mysql']->escape_string($root_id) . '
                    AND lang="' . lng() . '"
	        		AND news_id'.db::escape_string_where($GLOBALS['news_id']).'
	        ';
            $GLOBALS['mysql']->query($query);
            $result = $GLOBALS['mysql']->vysledek;
            $row = mysql_fetch_array($result);
        } while (!empty($row) &&  $row['reakcena_id'] != 0);
        if (!empty($row) && $row['reakcena_id'] == 0) {
            $query = '
                    SELECT *
                    FROM ' . VIEW_FORUM . '
                    WHERE id=' . $GLOBALS['mysql']->escape_string($root_id) . '
                    AND lang="' . lng() . '"
	        		AND news_id'.db::escape_string_where($GLOBALS['news_id']).'
	        ';
            $GLOBALS['mysql']->query($query);
            $row = $GLOBALS['mysql']->fetch_array();
	        echo '
        <ul class="comment-list">

            <li class="comment comment-parent">';
    	    echo get_html_prispevek($row, 1, 0);
        	echo vypis_forum_vlakna($root_id,1);
        	echo '
            </li>
        </ul>
';
        }
        
} else {
        echo vypis_forum_vlakna(0,$strana);
}
?>
    </div>
</div>
<div class="archive-pagination archive-pagination-bottom">

    
    

<?php
if ($strana > 1) {
	echo '<div class="left"><a href="' . odkaz2(null, array('page'=>($strana-1))) . '">&#171; Předchozí strana</a></div>&nbsp;| ';
}
for ( $i=max($strana-5,1); $i<=min($strana+5,$pocet_stranek); $i++ ) {
	if ( $i == $strana ) {
		echo $i;
	} else {
		echo '<a href="' . odkaz2(null, array('page'=>$i)) . '" title="' . lng('Strana ','Page ') . $i . '">' . $i . '</a>';
	}
	if ( $i < min($strana+5,$pocet_stranek) ) {
		echo "&nbsp;| ";
	}
}
if ($strana < $pocet_stranek) {
	echo '<div class="right"><a href="' . odkaz2(null, array('page'=>($strana+1))) . '">Následující strana &#187;</a></div>';
}
?>

    <div class="clearer">&nbsp;</div>

</div>

