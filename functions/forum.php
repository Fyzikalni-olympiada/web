<?php
if(!defined("VALID_ACCESS"))    {die("Neopr·vnÏn˝ p¯Ìstup!");}                         
//      Ochrana proti neopr·vnÏnÈmu p¯Ìstupu ke skript˘m

function zpracuj_form()
{
    $r = ''; //return

    /* Smazat p¯ÌspÏvek - musÌ b˝t p¯ihl·öen */
    if (isset($GLOBALS['get']['delete']) && je_opravnen($GLOBALS['get']['forum_id']) && isset($GLOBALS['get']['forum_id'])) {
        $GLOBALS['get']['delete'] = null;
        /* Kontrola ID v datab·zi */
        $GLOBALS['mysql']->query('
            SELECT id, reakcena_id
            FROM ' . TABLE_FORUM . '
            WHERE id=' . db::escape_string(db::odstran_problemy($GLOBALS['get']['forum_id'])) . '
        ');
        if ($row = $GLOBALS['mysql']->fetch_array()) {
            $forum_id = $row['id'];
            $reakcena_id = $row['reakcena_id'];
        } else {
            $GLOBALS['chyba'] .= lng('NeplanÈ ID p¯ÌspÏvku.','Invalid ID.') . '<br />';
        }

        /* Smaûe p¯ÌspÏvÏk a potomky p¯epojÌ na p¯edka */
        if ($GLOBALS['chyba'] == "") {
            $result = $GLOBALS['mysql']->query('
                SELECT id
                FROM ' . TABLE_FORUM . '
                WHERE reakcena_id=' . db::escape_string($forum_id) . '
            ');

            while((list($id) = mysql_fetch_array($result))) {
                $GLOBALS['mysql']->query('
                    UPDATE ' . TABLE_FORUM . '
                    SET reakcena_id=' . db::escape_string($reakcena_id) . '
                    WHERE id=' . db::escape_string($id) . '
                ');
            }

            $GLOBALS['mysql']->query('
                DELETE FROM ' . TABLE_FORUM . '
                WHERE id=' . db::escape_string($forum_id) . '
                LIMIT 1
            ');
            if ($smazano_pocet = mysql_affected_rows($GLOBALS['mysql']->dbc)) {
                $hlaska = lng('Smaz·n jeden p¯ÌspÏvek','The question was deleted');
            } else {
                $hlaska = lng('Nesmaz·n û·dn˝ p¯ÌspÏvek','No question was deleted');
            }
            $r .= '<p class="chyba">' . $hlaska . '</p>';
        }

    /** Odesl·n formul·¯ */
    } elseif (isset($_POST["ok"])) {

        if (!empty($_POST["title"])) {
            $_POST["title"] = ucfirst($_POST["title"]);
        } else {
            $GLOBALS['chyba'] .= lng('ChybÌ nadpis novinky!','Fill in the title!') . '<br />';
        }
        if (strtolower($_POST["guest_test"]) != GUEST_TEST_TEXT) {
            $GLOBALS['chyba'] .= lng('1 a 1 nenÌ','The name of our competition is not') . ' ' . strtoupper($_POST["guest_test"]) . lng(', ale 2',' but 2') . '!<br />';
        }
        if (!($err = spam($_POST["text"], $_POST["name"], $_POST["title"], $_POST["email"]))) {
        } else {
            $GLOBALS['chyba'] .= $err;
        }

        if ($GLOBALS['chyba'] == "") {

            /** Oöet¯enÌ pole text */
            $zaloha_text = $_POST["text"];
            $regex  = '@</?\w+((\s+\w+(\s*=\s*';
            $regex .= '(?:".*?"|\'.*?\'|[^\'">\s]+))?)+';
            $regex .= '\s*|\s*)/?>@i';
            $_POST["text"] = preg_replace($regex, '', $_POST["text"]);
            $_POST["text"] = preg_replace( "`((http)+(s)?:(//)|(www\.))((\w|\.|\-|_)+)(/)?(\S+)?`i", "<a href=\"http\\3://\\5\\6\\8\\9\" title=\"\\0\">\\5\\6\\8\\9</a>", $_POST["text"]);
            $_POST["text"] = ucfirst(vlnka($_POST["text"]));
            $_POST["text"] = str_replace("\r\n","<br />",$_POST["text"]);

            /** NastavenÌ cookies, pro automatickÈ vyplnÏnÌ polÌ name a email */
            setcookie('fo_forum[name]', $_POST['name'], mktime(0, 0, 0, 12, 31, 2020));
            setcookie('fo_forum[email]', $_POST['email'], mktime(0, 0, 0, 12, 31, 2020));
            setcookie('fo_forum[test]', $_POST['guest_test'], time()+(60*60*24*180));//vyprsi za pul roku

            /** Reakce na p¯ÌspÏvek nebo ˙prava - kontrola ID */
            $forum_id = null;
            $reakcena_id = 0;
            $news_id = $GLOBALS['news_id'];
            if (isset($_SESSION['id'])) {
                $users_id = $_SESSION['id'];
            } else {
                $users_id = null;
            }
            if ((isset($GLOBALS['get']['upravit']) || isset($GLOBALS['get']['reagovat'])) && isset($GLOBALS['get']['forum_id'])) {
                $GLOBALS['mysql']->query('
                    SELECT id, news_id
                    FROM ' . TABLE_FORUM . '
                    WHERE id=' . db::escape_string(db::odstran_problemy($GLOBALS['get']['forum_id'])) . '
                ');
                if ($row = $GLOBALS['mysql']->fetch_array()) {
                    if (isset($GLOBALS['get']['reagovat'])) {
                        $reakcena_id = $row['id'];
                        $news_id = $row['news_id']; /* news_id prevezmeme od predka */
                    } elseif (isset($_SESSION['id'])) { /* P¯ihl·öen˝ uûivatel edituje p¯ÌspÏvek */
                        $forum_id = $row['id'];
                    }
                }
            }

            /** Kdyz neni zadany email, nastavit ho na NULL */
            if ((empty($_POST["email"])) || !preg_match("/^[[:graph:]]+@[[:graph:]]+(\.[[:graph:]]{2,})$/", $_POST["email"])) {
                $_POST["email"] = null;
            }

            /** Nov˝ p¯ÌspÏvek */
            if (empty($forum_id)) {
                $GLOBALS['mysql']->query("
                    INSERT INTO `" . TABLE_FORUM . "`
                        ( `name`, `email`, `title`, `text`, `reakcena_id`, `news_id`, `users_id`, `lang`, `who` )
                    VALUES (
                        " .
                        db::escape_string(db::odstran_problemy($_POST["name"])) . ", " .
                        db::escape_string(db::odstran_problemy($_POST["email"])) . ", " .
                        db::escape_string(db::odstran_problemy($_POST["title"])) . ", " .
                        db::escape_string(db::odstran_problemy($_POST["text"])) . ", " .
                        db::escape_string($reakcena_id) . ", " .
                        db::escape_string($news_id) . ", " .
                        db::escape_string($users_id) . ", " .
                        db::escape_string(lng()) . ", " .
                        db::escape_string($GLOBALS['kdo']) . "
                    )
                ");

                $forum_last_id = mysql_insert_id($GLOBALS['mysql']->dbc);

                $telo = 'Na ' . SERVER_NAME . ' je novy diskusni prispevek od ' . $_POST['name'] . ' (' . $_POST['email'] . "):\n\n" . $_POST['title'] . "\n\n" . $zaloha_text . "\n\nhttp://" . SERVER_NAME . ROOT_WWW . FILE_INDEX . '?file=' . $_GET['file'] . '&forum_id=' . $forum_last_id . '&sort=vlakno&news_id=' . $GLOBALS['news_id'];

                $GLOBALS['mysql']->query('
                    SELECT email
                    FROM ' . TABLE_USERS . '
                    WHERE send_forum="1"
                ');
                while ($row = $GLOBALS['mysql']->fetch_array()) {
                    @mail($row['email'], 'Novy diskusni prispevek na ' . SERVER_NAME, $telo, MAIL_HEADERS);
                }

                /** Reakce na jiny prispevek, tak poöleme email */
                if (!empty($reakcena_id)) {
                    $GLOBALS['mysql']->query("
                        SELECT name, email, title, datum_cas
                        FROM " . VIEW_FORUM . "
                        WHERE id=" . db::escape_string($reakcena_id)
                    );
                    $row = $GLOBALS['mysql']->fetch_array();
                    if (!is_null($row['email'])) {
                        $datum = explode('-',$row["date"]);
                        $telo = "Dobry den " . $row["name"] . ".\n\nV diskusnim foru na " . SERVER_NAME . " byla pridana nova reakce na Vas pripevek \"" . $row["title"] . "\" z " . $row['datum_cas'] . "\n\nhttp://" . SERVER_NAME . ROOT_WWW . odkaz2('content/forum.php', array('forum_id' => $forum_last_id, 'sort' => 'vlakno', 'news_id' => $GLOBALS['news_id']), 0);
                        @mail($row['email'], "Reakce na vas prispevek ve foru " . SERVER_NAME, $telo, MAIL_HEADERS);
                    }
                }

            /** Editace p¯ÌspÏvku */
            } elseif (isset($GLOBALS['get']['upravit']) && je_opravnen($forum_id)) {
                $GLOBALS['mysql']->query('
                    UPDATE ' . TABLE_FORUM . ' SET
                        name=' . db::escape_string(db::odstran_problemy($_POST['name'])) . ',
                        email=' . db::escape_string(db::odstran_problemy($_POST['email'])) . ',
                        title=' . db::escape_string(db::odstran_problemy($_POST['title'])) . ',
                        text=' . db::escape_string(db::odstran_problemy($zaloha_text)) . '
                    WHERE id=' . db::escape_string($forum_id) . '
                ');
                /** Zmeni news_id u celeho vlakna - pouze administr·tor */
                if (isset($_POST['news_id']) && je_korenovy_prispevek($forum_id) && (isset($_SESSION['administrator']) && $_SESSION['administrator'] == 1) ) {
                    if ($_POST['news_id'] == 0) {
                        $news_id = null;
                    } else {
                        $news_id = $_POST['news_id'];
                    }
                    nastav_news_id_strom($forum_id, $news_id);
                }
            }

            $r .= '
    <p style="text-align: center">P¯id·nÌ n·zoru probÏhlo v po¯·dku.</p>';

            /** P¯i zobrazenÌ jedinÈho vl·kna, ponechat $forum_id v GET */
            if (!isset($GLOBALS['get']['sort']) || $GLOBALS['get']['sort'] != 'vlakno') {
                $forum_id = null;
            }
            header('Location: ' . odkaz2(null, array('reagovat'=>null,'upravit'=>null,'forum_id'=>$forum_id), 0));
            ob_end_clean();
            exit();

        } else {
                $r .= '
    <p class="chyba">
        P¯id·nÌ se nezda¯ilo!<br /><br />
        ' . $GLOBALS['chyba'] . '<br />
        Opravte chyby a odeölete formul·¯ znovu.
    </p>';
        }
    }

    return $r;
}

function vypis_forum_vlakna($reakcena_id,$stranka)
{
    /* Nejprve zjistÌme, kolik je reakci*/
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

    /* VypÌöeme reakce (rekurzivne) */
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
    if ( $reakcena_id == 0 ) { //ko¯enovÈ vl·kno
        $class_ul = ' class="comment-list"';
    } else {
        $class_ul = ' class="children"';
    }
    $s = '
<ul'.$class_ul.'>';
    while ( $row = mysql_fetch_array($result) ) {
        $vlakno = vypis_forum_vlakna($row["id"],$stranka);
        if ( $reakcena_id == 0 ) { //ko¯enov˝ p¯ÌspÏvek
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
    /* Nejprve zjistÌme, kolik je prispevku*/
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
    $text = str_replace("Ï", "e", $text);
    $text = str_replace("ö", "s", $text);
    $text = str_replace("Ë", "c", $text);
    $text = str_replace("¯", "r", $text);
    $text = str_replace("û", "z", $text);
    $text = str_replace("˝", "y", $text);
    $text = str_replace("·", "a", $text);
    $text = str_replace("Ì", "i", $text);
    $text = str_replace("È", "e", $text);
    $text = str_replace("˙", "u", $text);
    $text = str_replace("˘", "u", $text);
    $text = str_replace("ù", "t", $text);
    $text = str_replace("Ú", "n", $text);
    $text = str_replace("Ô", "d", $text);
    if (empty($name)) {
        $r .= lng('UveÔte prosÌm Vaöe jmÈno!','Fill in your name!') . '<br />';
    }
    /** Email n·m nevadÌ, kdyû nenÌ vyplnÏn˝. */
    if (!empty($mail) &&!EReg("^[[:graph:]]+@[[:graph:]]+(\.[[:graph:]]{2,})$", $mail)) {
        $r .= lng('V·ö e-mail m· neplatn˝ form·t!','Your e-mail is invalid!') . '<br />';
    }
    if (strlen($text) < 3) {
        $r .= lng('V·ö text je p¯Ìliö kr·tk˝!','Your text is too short!') . '<br />';
    }
    if (strlen($text) > 5000) {
        $r .= lng('V·ö text je p¯Ìliö dlouh˝!','Your text is too long!') . '<br />';
    }
    if (stristr($text, "kurv") || strstr($text, "prdel") || strstr($text, "pica") || strstr($text, "pice") || strstr($text, "pici") || strstr($text, "hajzl") || strstr($text, "debil") || strstr($text, "kokot") || strstr($text, "curak") || strstr($text, "kreten") || strstr($text, "srack") || strstr($text, "srat") || strstr($text, "serte") || strstr($text, "serou") || strstr($text, "srany") || strstr($text, "srani") || strstr($text, "srane") || strstr($text, " kund") || strstr($text, "curac") || strstr($text, "jebat") || strstr($text, "jeban") || strstr($text, "kurev") || strstr($text, "sukat") || strstr($text, " hovn") || strstr($text, "mrd") || strstr($text, "pazdrat") || strstr($text, "sragor") || strstr($text, "prdol") || strstr($text, "chuj") || strstr($text, "klatic") || strstr($text, "honimir") || strstr($text, "hulibrk") || strstr($text, "chcat") || strstr($text, "chcan") || strstr($text, "sulin")) {
        $r .= lng('NepouûÌvejte prosÌm v textu sprost· slova!','Please, do not use dirty words!') . '<br />';
    }
    return $r;
}

function posli_forum_digest()
{

}


/**
 * UloûÌ do tabulky fykos.users Ëas poslednÌ n·vötÏvy fÛra
 */
function uloz_cas_posledni_navstevy()
{
    if (!empty($_SESSION['id'])) {
        $GLOBALS['mysql']->query('
            UPDATE ' . TABLE_USERS . ' SET
            last_forum_visit_datetime=NOW()
            WHERE id=' . db::escape_string($_SESSION['id']) . '
        ');
        return true;
    } else {
        return false;
    }
}


/**
*       Form·t $row
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
        $s_email = '<a href="e-mail:' . $row["email"] . '" title="' . lng('Autor p¯ÌspÏvku','Author of the post') . '">' . nahrad_smajliky($row["name"]) . '</a>';
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
                <span class="text-separator">|</span> <a href="' . odkaz2(null, array('forum_id'=>$row['id'],'reagovat'=>1,'delete'=>null,'upravit'=>null)) . '">Reagovat &#187;</a></div>
';
    if ($GLOBALS['sort'] != 'vlakno') {
        $s .= '
            <div class="right"><a href="' . odkaz2(null, array('forum_id'=>$row["id"],'sort'=>'vlakno')) . '">#'.$row['id'].'</a></div>';
    }
    $s .= '
            <div class="clearer">&nbsp;</div>

        </div>

        <div class="comment-text">
            <p><span class="large">' . $row['title'] . '</span><br />' . nahrad_smajliky($row["text"]) . '</p>
        </div>

    </div>

</div>
';

    return $s;      
}

/**
*    Smaûe p¯ÌspÏvek i vöechny potomky
*/
function smazat_prispevek_strom($forum_id)
{
    $result = $GLOBALS['mysql']->query('
        SELECT id
        FROM ' . TABLE_FORUM . '
        WHERE reakcena_id=' . db::escape_string($forum_id) . '
    ');

    while((list($id) = mysql_fetch_array($result))) {
        smazat_prispevek_strom($id);
    }

    $GLOBALS['mysql']->query('
        DELETE FROM ' . TABLE_FORUM . '
        WHERE id=' . db::escape_string($forum_id) . '
    ');
    return $GLOBALS['smazano']++;
}

/**
*    ZmÏnÌ news_id u celeho vlakna pripojeneho k $forum_id
*    Pri chybe vrati 0, jinak pocet presunutych prispevku
*/
function nastav_news_id_strom($forum_id, $news_id)
{
    $presunuto = 0;

    /** kontrola news_id */
    if (!is_null($news_id)) {
        $GLOBALS['mysql']->query('
            SELECT id
            FROM ' . TABLE_NEWS . '
            WHERE id=' . db::escape_string($news_id) . '
        ');
        if ($row = $GLOBALS['mysql']->fetch_array()) {
            $news_id = $row['id'];
        } else {
            // Chybny vstup, nic neprovede a vrati 0
            return 0;
        }
    }

    $result = $GLOBALS['mysql']->query('
        SELECT id
        FROM ' . TABLE_FORUM . '
        WHERE reakcena_id=' . db::escape_string($forum_id) . '
    ');

    while((list($id) = mysql_fetch_array($result))) {
        $presunuto += nastav_news_id_strom($id, $news_id);
    }

    $GLOBALS['mysql']->query('
        UPDATE ' . TABLE_FORUM . '
        SET news_id=' . db::escape_string($news_id) . '
        WHERE id=' . db::escape_string($forum_id) . '
    ');
    $presunuto++;
    return $presunuto;
}

/**
*    ZjistÌ, zda je p¯ÌspÏvÏk ko¯enov˝, tedy jestli reakcena_id == 0
*/
function je_korenovy_prispevek($forum_id)
{
    $GLOBALS['mysql']->query('
        SELECT reakcena_id
        FROM ' . TABLE_FORUM . '
        WHERE id' . db::escape_string_where($forum_id) . '
    ');
    if ($row = $GLOBALS['mysql']->fetch_array()) {
        if ($row['reakcena_id'] == 0) {
            $r = true;
        } else {
            $r = false;
        }
    } else { /* Pro chybny vstup vrati take false */
        $r = false;
    }
    return $r;
}


/**
 * ZjistÌ, zda m· p¯ihl·öen˝ uûivatel pr·vo UPDATE a DELETE na $forum_id
 */
function je_opravnen($forum_id)
{
    // Administator
    if (isset($_SESSION['administrator']) && $_SESSION['administrator'] == 1) {
        return true;
    }

    // nebo autor prispevku
    if (isset($_SESSION['id'])) {
        $GLOBALS['mysql']->query('
            SELECT users_id FROM ' . TABLE_FORUM . '
            WHERE id=' . db::escape_string($forum_id) . '
        ');
        if (list($users_id) = $GLOBALS['mysql']->fetch_array()) {
            if ( !is_null($users_id) && $_SESSION['id'] == $users_id) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
*       V textu nahradi textove smajliky obrazkama
*/
function nahrad_smajliky($text) 
{
    return $text;
}

?>