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
forum_guest_test_start_session();

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

posli_forum_digest();
uloz_cas_posledni_navstevy();

$chyba = '';
$zpracuj_form = zpracuj_form();

if ($kdo == "student")
	echo '
	<h2>Diskusní fórum pro studenty</h2>
	<p>Toto diskusní fórum je určeno pro studenty. Můžete se zde vyjadřovat ohledně průběhu olympiády, případně mít nějaké jiné připomínky či dotazy.';
if ($kdo == "ucitel")
	echo '
	<h2>Diskusní fórum pro učitele</h2>
	<p>Toto diskusní fórum je určeno pro učitele. Můžete se zde vyjadřovat ohledně průběhu olympiády, případně mít nějaké jiné připomínky či dotazy.';
if ($kdo == "organizator")
	echo '
	<p>Toto diskusní fórum je určeno pro organizátory. Můžete se zde vyjadřovat ohledně průběhu olympiády, kvality úloh, případně mít nějaké jiné připomínky či dotazy.';

echo '
</p>';


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

$title = null;
$text = null;
$name = null;
$email = null;
$guest_test = null;

if (!empty($chyba) || isset($_POST['ok'])) { 
	/* Chyba nebo odeslán formulář */
	if (isset($_POST['title'])) {
		$title = db::odstran_problemy($_POST['title']);
	}
	if (isset($_POST['text'])) {
		$text = db::odstran_problemy($_POST['text']);
	}
	if (isset($_POST['name'])) {
		$name = db::odstran_problemy($_POST['name']);
	}
	if (isset($_POST['email'])) {
		$email = db::odstran_problemy($_POST['email']);
	}
	if (isset($_POST['guest_test'])) {
		$guest_test = db::odstran_problemy($_POST['guest_test']);
	}
} else {
	/** Prvni moznost je natahnout to z Cookies */
	if (isset($_COOKIE['fo_forum']['name'])) {
		$name = $_COOKIE['fo_forum']['name'];
	}
	if (isset($_COOKIE['fo_forum']['email'])) {
		$email = $_COOKIE['fo_forum']['email'];
	}

	/** Nekdo je nalogovany */
	if (isset($_SESSION['id'])) {
		$name = $_SESSION['nickname'];
		$email = $_SESSION['email'];
	}

	/** Reakce nebo uprava */
	if (isset($GLOBALS['get']['forum_id']) && (isset($GLOBALS['get']['upravit']) || isset($GLOBALS['get']['reagovat']))) {
		$result = $GLOBALS['mysql']->query('
			SELECT id, title, text, name, email, users_id
			FROM ' . TABLE_FORUM . '
			WHERE id=' . db::escape_string($GLOBALS['get']['forum_id']) . '
			AND lang="' . lng() . '"
		');
		if ($row = mysql_fetch_array($result)) {
			/** Reakce na prispevek */
			if (isset($GLOBALS['get']['reagovat'])) {
				if (preg_match("/^Re:/i", $row['title'])) {
					$title = $row['title'];
				} else {
					$title = 'Re: ' . $row['title'];
				}
				
			/** Úprava příspěvku (musí být nalogován nebo administrátor) */
			} elseif( (isset($_SESSION['id']) && !is_null($row['users_id']) && $_SESSION['id'] == $row['users_id']) 
				|| (isset($_SESSION['administrator']) && $_SESSION['administrator'] == 1) ) {
				$title = $row['title'];
				$text = $row['text'];
				$name = $row['name'];
				$email = $row['email'];
			}
		} else {
			$GLOBALS['get']['reagovat'] = null;
			$GLOBALS['get']['upravit'] = null;
			$GLOBALS['get']['forum_id'] = null;
		}		

	}
}

echo $zpracuj_form;

echo '
<div class="legend" id="comment-form-title">
	<h3>Vložit příspěvek</h3>

            <form class="form-horizontal" action="' . odkaz2() . '" method="post" id="commentform">

                    <div class="form-group">
                        <label for="title" class="sr-only">Nadpis</label>
                        <div class="col-sm-12"><input type="text" name="title" id="title" value="' . $title . '" tabindex="1" class="form-control" placeholder="Nadpis" /></div>
                    </div>

					<div class="form-group">
						<div class="col-sm-12">
							<textarea class="form-control" placeholder="Text" name="text" id="comment" cols="10" rows="10" tabindex="2">' . $text . '</textarea>
						</div>
					</div>
';
if (isset($_SESSION['id'])) {
	echo '
	                    <input type="hidden" name="guest_test" value="" />';
} else {
	$guest_test_data = forum_guest_test_get();
	echo '
	                    <div class="form-group">
	                        <label for="guest_test" class="col-sm-8">Kontrolní příklad: ' . $guest_test_data['question'] . '</label>
	                        <div class="col-sm-4"><input type="text" name="guest_test" id="guest_test" value="' . $guest_test . '"  tabindex="5" class="form-control" placeholder="Výsledek" /></div>
	                    </div>';
}
echo '
                    <div class="form-group">
                        <label for="author" class="col-sm-2">Jméno</label>
                        <div class="col-sm-10"><input type="text" name="name" id="name" value="' . $name . '" tabindex="3" class="form-control" /></div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="col-sm-2">Email</label>
                        <div class="col-sm-10"><input type="email" name="email" id="email" value="' . $email . '" tabindex="4" class="form-control" /></div>
                    </div>

					<input type="hidden" name="ok" value=1/>
					<button type="submit" class="btn btn-default">Odeslat</button>
            </form>
</div>';

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

