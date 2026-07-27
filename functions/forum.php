<?php
if(!defined("VALID_ACCESS"))    {die("Neoprávněný přístup!");}                         
//      Ochrana proti neoprávněnému přístupu ke skriptům

function zpracuj_form()
{
    $r = ''; //return

    /** Odeslán formulář */
    if (isset($_POST["ok"])) {

        if (!empty($_POST["title"])) {
            $_POST["title"] = ucfirst($_POST["title"]);
        } else {
            $GLOBALS['chyba'] .= lng('Chybí nadpis novinky!','Fill in the title!') . '<br />';
        }
        if (!($err = spam($_POST["text"], $_POST["name"], $_POST["title"], $_POST["email"]))) {
        } else {
            $GLOBALS['chyba'] .= $err;
        }

        if ($GLOBALS['chyba'] == "") {

            /** Ošetření pole text */
			$_POST["text"] = stripslashes($_POST["text"]);
            $regex  = '@</?\w+((\s+\w+(\s*=\s*';
            $regex .= '(?:".*?"|\'.*?\'|[^\'">\s]+))?)+';
            $regex .= '\s*|\s*)/?>@i';
            $_POST["text"] = preg_replace($regex, '', $_POST["text"]);
            $_POST["text"] = preg_replace( "`((http)+(s)?:(//)|(www\.))((\w|\.|\-|_)+)(/)?(\S+)?`i", "<a href=\"http\\3://\\5\\6\\8\\9\" title=\"\\0\">\\5\\6\\8\\9</a>", $_POST["text"]);
            $_POST["text"] = ucfirst(vlnka($_POST["text"]));
            $_POST["text"] = str_replace("\r\n","<br />",$_POST["text"]);

            /** Nastavení cookies, pro automatické vyplnění polí name a email */
            setcookie('fo_forum[name]', $_POST['name'], forum_cookie_expiration());
            setcookie('fo_forum[email]', $_POST['email'], forum_cookie_expiration());

            /** Reakce na příspěvek - kontrola ID */
            $reakcena_id = 0;
            $news_id = $GLOBALS['news_id'];
            if (isset($GLOBALS['get']['reagovat']) && isset($GLOBALS['get']['forum_id'])) {
                $GLOBALS['mysql']->query('
                    SELECT id, news_id
                    FROM ' . TABLE_FORUM . '
                    WHERE id=' . db::escape_string(db::odstran_problemy($GLOBALS['get']['forum_id'])) . '
                ');
                if ($row = $GLOBALS['mysql']->fetch_array()) {
                    $reakcena_id = $row['id'];
                    $news_id = $row['news_id']; /* news_id prevezmeme od predka */
                }
            }

            /** Kdyz neni zadany email, nastavit ho na NULL */
            if ((empty($_POST["email"])) || !preg_match("/^[[:graph:]]+@[[:graph:]]+(\.[[:graph:]]{2,})$/", $_POST["email"])) {
                $_POST["email"] = null;
            }

            /** Nový příspěvek */
            $GLOBALS['mysql']->query("
                INSERT INTO `" . TABLE_FORUM . "`
                    ( `name`, `email`, `title`, `text`, `reakcena_id`, `news_id`, `users_id`, `lang`, `who` )
                VALUES (
                    " .
                    db::escape_string(db::odstran_problemy($_POST["name"])) . ", " .
                    db::escape_string(db::odstran_problemy($_POST["email"])) . ", " .
                    db::escape_string(db::odstran_problemy($_POST["title"])) . ", " .
                    db::escape_string($_POST["text"]) . ", " .
                    db::escape_string($reakcena_id) . ", " .
                    db::escape_string($news_id) . ", " .
                    "NULL, " .
                    db::escape_string(lng()) . ", " .
                    db::escape_string($GLOBALS['kdo']) . "
                )
            ");

            $r .= '
    <p style="text-align: center">Přidání názoru proběhlo v pořádku.</p>';

            header('Location: ' . odkaz2(null, array('reagovat'=>null,'forum_id'=>null), 0));
            ob_end_clean();
            exit();

        } else {
                $r .= '
    <p class="chyba">
        Přidání se nezdařilo!<br /><br />
        ' . $GLOBALS['chyba'] . '<br />
        Opravte chyby a odešlete formulář znovu.
    </p>';
        }
    }

    return $r;
}

function forum_cookie_expiration()
{
    return time()+(60*60*24*180);
}

function vypis_forum_vlakna($reakcena_id,$stranka)
{
    /* Nejprve zjistíme, kolik je reakci*/
    $query = '
        SELECT COUNT(id)
        FROM ' . VIEW_FORUM . '
        WHERE reakcena_id=' . $reakcena_id . '
        AND lang="' . lng() . '"
           AND news_id'.db::escape_string_where($GLOBALS['news_id']).'
        AND who='. db::escape_string($GLOBALS['kdo']).'
    ';
    $GLOBALS['mysql']->query($query);
    list($pocet_reakci) = $GLOBALS['mysql']->fetch_array();
    if ($pocet_reakci == 0) {
        return '';
    }

    /* Vypíšeme reakce (rekurzivne) */
    $query = '
        SELECT *
        FROM ' . VIEW_FORUM . '
        WHERE reakcena_id=' . $reakcena_id . '
        AND lang="' . lng() . '"
        AND who='. db::escape_string($GLOBALS['kdo']).'
           AND news_id'.db::escape_string_where($GLOBALS['news_id']);
    if ( $reakcena_id == 0 ) {
        $query .= '
        ORDER BY posted_timestamp DESC
        LIMIT ?, ?';

        $GLOBALS['mysql']->query('SET @skip=(' . ($stranka-1) . '*(SELECT value FROM ' . TABLE_CONSTANTS . ' WHERE name="POCET_PRISPEVKU_NA_STRANKU"))');
        $GLOBALS['mysql']->query('SET @numrows=(SELECT value FROM ' . TABLE_CONSTANTS . ' WHERE name="POCET_PRISPEVKU_NA_STRANKU")');
        $GLOBALS['mysql']->query('PREPARE STMT FROM \'' . $query . '\'');
        $query = 'EXECUTE STMT USING @skip, @numrows';
    } else {
        $query .= '
        ORDER BY posted_timestamp';
    }
    $GLOBALS['mysql']->query($query);
    $result = $GLOBALS['mysql']->vysledek;
    $pocet = 1;
    if ( $reakcena_id == 0 ) { //kořenové vlákno
        $class_ul = ' class="comment-list"';
    } else {
        $class_ul = ' class="children"';
    }
    $s = '
<ul'.$class_ul.'>';
    while ( $row = mysql_fetch_array($result) ) {
        $vlakno = vypis_forum_vlakna($row["id"],$stranka);
        if ( $reakcena_id == 0 ) { //kořenový příspěvek
            $class = 'comment-parent';
        } elseif ($pocet == $pocet_reakci) {
            $class = 'comment'; //posledni
        } else {
            $class = 'comment';
        }
        $s .= '
    <li class="' . $class . '">';
        $s .= get_html_prispevek($row);
        $s .= $vlakno;
        $s .= '
    </li>';
        $pocet++;
    }
    $s .= '
</ul>';

    return $s;
}

function vypis_forum_chronologicky($stranka)
{
    /* Nejprve zjistíme, kolik je prispevku*/
    $query = '
        SELECT COUNT(id)
        FROM ' . VIEW_FORUM . '
        WHERE lang="' . lng() . '"
           AND news_id'.db::escape_string_where($GLOBALS['news_id']).'
        AND who='. db::escape_string($GLOBALS['kdo']).'
    ';
    $GLOBALS['mysql']->query($query);
    list($pocet_reakci) = $GLOBALS['mysql']->fetch_array();
    /*if ($pocet_reakci < (($stranka-1)*$nastranku)) {
        return '';
    }*/

    $query = '
        SELECT *
        FROM ' . VIEW_FORUM . '
        WHERE lang="' . lng() . '"
           AND news_id'.db::escape_string_where($GLOBALS['news_id']).'
        AND who='. db::escape_string($GLOBALS['kdo']).'
        ORDER BY posted_timestamp DESC
        LIMIT ?, ?';

    $GLOBALS['mysql']->query('SET @skip=(' . ($stranka-1) . '*(SELECT value FROM ' . TABLE_CONSTANTS . ' WHERE name="POCET_PRISPEVKU_NA_STRANKU_CHRON"))');
    $GLOBALS['mysql']->query('SET @numrows=(SELECT value FROM ' . TABLE_CONSTANTS . ' WHERE name="POCET_PRISPEVKU_NA_STRANKU_CHRON")');
    $GLOBALS['mysql']->query('PREPARE STMT FROM \'' . $query . '\'');
    $GLOBALS['mysql']->query('EXECUTE STMT USING @skip, @numrows');
    $result = $GLOBALS['mysql']->vysledek;
    $s = '
<ul class="comment-list">';
    $pocet = 1;
    while ( $row = mysql_fetch_array($result) ) {
        $s .= '
    <li class="comment-parent">';
        $s .= get_html_prispevek($row);
        $s .= '
    </li>';
        $pocet++;
    }
    $s .= '
</ul>';

    return $s;
}

/** Function spam returns 0 if $text is not a recognizable spam, else it returns error message:
*       1 - no name,
*       2 - not a valid e-amil,
*       3 - it is too long or short,
*       4 - dirty word is used in it
*/
function spam($text, $name, $titleline, $mail)
{
    $r = '';
    $text = $text . " " .  $name . " " . $titleline;
    $t_spam = $text . " " .  $name;
    $text = strtolower($text);
    $text = str_replace("ě", "e", $text);
    $text = str_replace("š", "s", $text);
    $text = str_replace("č", "c", $text);
    $text = str_replace("ř", "r", $text);
    $text = str_replace("ž", "z", $text);
    $text = str_replace("ý", "y", $text);
    $text = str_replace("á", "a", $text);
    $text = str_replace("í", "i", $text);
    $text = str_replace("é", "e", $text);
    $text = str_replace("ú", "u", $text);
    $text = str_replace("ů", "u", $text);
    $text = str_replace("ť", "t", $text);
    $text = str_replace("ň", "n", $text);
    $text = str_replace("ď", "d", $text);
    if (empty($name)) {
        $r .= lng('Uveďte prosím Vaše jméno!','Fill in your name!') . '<br />';
    }
    /** Email nám nevadí, když není vyplněný. */
    if (!empty($mail) &&!EReg("^[[:graph:]]+@[[:graph:]]+(\.[[:graph:]]{2,})$", $mail)) {
        $r .= lng('Váš e-mail má neplatný formát!','Your e-mail is invalid!') . '<br />';
    }
    if (strlen($text) < 3) {
        $r .= lng('Váš text je příliš krátký!','Your text is too short!') . '<br />';
    }
    if (strlen($text) > 5000) {
        $r .= lng('Váš text je příliš dlouhý!','Your text is too long!') . '<br />';
    }
    if (stristr($text, "kurv") || strstr($text, "prdel") || strstr($text, "pica") || strstr($text, "pice") || strstr($text, "pici") || strstr($text, "hajzl") || strstr($text, "debil") || strstr($text, "kokot") || strstr($text, "curak") || strstr($text, "kreten") || strstr($text, "srack") || strstr($text, "srat") || strstr($text, "serte") || strstr($text, "serou") || strstr($text, "srany") || strstr($text, "srani") || strstr($text, "srane") || strstr($text, " kund") || strstr($text, "curac") || strstr($text, "jebat") || strstr($text, "jeban") || strstr($text, "kurev") || strstr($text, "sukat") || strstr($text, " hovn") || strstr($text, "mrd") || strstr($text, "pazdrat") || strstr($text, "sragor") || strstr($text, "prdol") || strstr($text, "chuj") || strstr($text, "klatic") || strstr($text, "honimir") || strstr($text, "hulibrk") || strstr($text, "chcat") || strstr($text, "chcan") || strstr($text, "sulin")) {
        $r .= lng('Nepoužívejte prosím v textu sprostá slova!','Please, do not use dirty words!') . '<br />';
    }
    return $r;
}

function posli_forum_digest()
{

}


/**
*       Formát $row
*               * SELECT * FROM V_forum
*/
function get_html_prispevek($row)
{
    $s = '';
    if ($row['online'] == 1) {
        $extra = ' class="online"';
    } elseif ($row['actual'] == 1) {
        $extra = ' class="actual"';
    } else {
        $extra = '';
    }
    if (preg_match("/@fykos.mff.cuni.cz$/", $row["email"]) || $row['organizator'] == 1) { //email organizatora nebo organizator
        $extra_dt = ' class="org"';
    } else {
        $extra_dt = '';
    }
    if (!is_null($row['email'])) {
        $row["email"] = str_replace("@", "(zavinac)", $row["email"]);
        $s_email = '<a href="e-mail:' . $row["email"] . '" title="' . lng('Autor příspěvku','Author of the post') . '">' . nahrad_smajliky($row["name"]) . '</a>';
    } else {
        $s_email = nahrad_smajliky($row["name"]);
    }

    $s .= '
<div class="comment-content"' . $extra . '>

    <div class="comment-body">

        <div class="post-date">

            <div class="left"><img src="images/sample-gravatar.gif" height="14" width="14" alt="" />
                <span class="loud">' . $s_email . '</span>
                &ndash; ' . $row['datum_cas'] . '
                <span class="text-separator">|</span> <a href="' . odkaz2(null, array('forum_id'=>$row['id'],'reagovat'=>1)) . '">Reagovat &#187;</a></div>
';
    if ($GLOBALS['sort'] != 'vlakno') {
        $s .= '
            <div class="right"><a href="' . odkaz2(null, array('forum_id'=>$row["id"],'sort'=>'vlakno')) . '">#'.$row['id'].'</a></div>';
    }
    $s .= '
            <div class="clearer">&nbsp;</div>

        </div>

        <div class="comment-text">
            <p><span class="large">' . htmlspecialchars($row['title'], ENT_NOQOUTES | ENT_XHTML) . '</span><br />' . nahrad_smajliky($row["text"]) . '</p>
        </div>

    </div>

</div>
';

    return $s;      
}

/**
*       V textu nahradi textove smajliky obrazkama
*/
function nahrad_smajliky($text) 
{
    return $text;
}

?>
