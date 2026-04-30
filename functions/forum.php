<?php
if(!defined("VALID_ACCESS"))    {die("Neoprávněný přístup!");}                         
//      Ochrana proti neoprávněnému přístupu ke skriptům

function zpracuj_form()
{
    $r = ''; //return

    /* Smazat příspěvek - musí být přihlášen */
    if (isset($GLOBALS['get']['delete']) && je_opravnen($GLOBALS['get']['forum_id']) && isset($GLOBALS['get']['forum_id'])) {
        $GLOBALS['get']['delete'] = null;
        /* Kontrola ID v databázi */
        $GLOBALS['mysql']->query('
            SELECT id, reakcena_id
            FROM ' . TABLE_FORUM . '
            WHERE id=' . db::escape_string(db::odstran_problemy($GLOBALS['get']['forum_id'])) . '
        ');
        if ($row = $GLOBALS['mysql']->fetch_array()) {
            $forum_id = $row['id'];
            $reakcena_id = $row['reakcena_id'];
        } else {
            $GLOBALS['chyba'] .= lng('Neplané ID příspěvku.','Invalid ID.') . '<br />';
        }

        /* Smaže příspěvěk a potomky přepojí na předka */
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
                $hlaska = lng('Smazán jeden příspěvek','The question was deleted');
            } else {
                $hlaska = lng('Nesmazán žádný příspěvek','No question was deleted');
            }
            $r .= '<p class="chyba">' . $hlaska . '</p>';
        }

    /** Odeslán formulář */
    } elseif (isset($_POST["ok"])) {

        if (!empty($_POST["title"])) {
            $_POST["title"] = ucfirst($_POST["title"]);
        } else {
            $GLOBALS['chyba'] .= lng('Chybí nadpis novinky!','Fill in the title!') . '<br />';
        }
        if (!forum_guest_test_check(isset($_POST["guest_test"]) ? $_POST["guest_test"] : null)) {
            $GLOBALS['chyba'] .= lng('Kontrolní fyzikální příklad není vypočítaný správně. Uveďte prosím pouze číselnou hodnotu.','The anti-spam physics example is not solved correctly. Please enter only the number.') . '<br />';
        }
        if (!($err = spam($_POST["text"], $_POST["name"], $_POST["title"], $_POST["email"]))) {
        } else {
            $GLOBALS['chyba'] .= $err;
        }

        if ($GLOBALS['chyba'] == "") {

            /** Ošetření pole text */
			$_POST["text"] = stripslashes($_POST["text"]);
            $zaloha_text = $_POST["text"];
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
            forum_guest_test_reset();

            /** Reakce na příspěvek nebo úprava - kontrola ID */
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
                    } elseif (isset($_SESSION['id'])) { /* Přihlášený uživatel edituje příspěvek */
                        $forum_id = $row['id'];
                    }
                }
            }

            /** Kdyz neni zadany email, nastavit ho na NULL */
            if ((empty($_POST["email"])) || !preg_match("/^[[:graph:]]+@[[:graph:]]+(\.[[:graph:]]{2,})$/", $_POST["email"])) {
                $_POST["email"] = null;
            }

            /** Nový příspěvek */
            if (empty($forum_id)) {
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
                        db::escape_string($users_id) . ", " .
                        db::escape_string(lng()) . ", " .
                        db::escape_string($GLOBALS['kdo']) . "
                    )
                ");

                $forum_last_id = mysql_insert_id($GLOBALS['mysql']->dbc);

                $telo = 'Na ' . SERVER_NAME . ' je novy diskusni prispevek od ' . $_POST['name'] . ' (' . $_POST['email'] . "):\n\n" . $_POST['title'] . "\n\n" . $zaloha_text . "\n\nhttp://" . SERVER_NAME . rtrim(ROOT_WWW, '/') . odkaz2('content/forum.php', array('forum_id' => $forum_last_id, 'sort' => 'vlakno', 'news_id' => $GLOBALS['news_id']), 0);

                $GLOBALS['mysql']->query('
                    SELECT email
                    FROM ' . TABLE_USERS . '
                    WHERE send_forum="1"
                ');
                while ($row = $GLOBALS['mysql']->fetch_array()) {
                    @mail($row['email'], 'Novy diskusni prispevek na ' . SERVER_NAME, $telo, MAIL_HEADERS);
                }

                /** Reakce na jiny prispevek, tak pošleme email */
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

            /** Editace příspěvku */
            } elseif (isset($GLOBALS['get']['upravit']) && je_opravnen($forum_id)) {
                $GLOBALS['mysql']->query('
                    UPDATE ' . TABLE_FORUM . ' SET
                        name=' . db::escape_string(db::odstran_problemy($_POST['name'])) . ',
                        email=' . db::escape_string(db::odstran_problemy($_POST['email'])) . ',
                        title=' . db::escape_string(db::odstran_problemy($_POST['title'])) . ',
                        text=' . db::escape_string(db::odstran_problemy($zaloha_text)) . '
                    WHERE id=' . db::escape_string($forum_id) . '
                ');
                /** Zmeni news_id u celeho vlakna - pouze administrátor */
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
    <p style="text-align: center">Přidání názoru proběhlo v pořádku.</p>';

            /** Při zobrazení jediného vlákna, ponechat $forum_id v GET */
            if (!isset($GLOBALS['get']['sort']) || $GLOBALS['get']['sort'] != 'vlakno') {
                $forum_id = null;
            }
            header('Location: ' . odkaz2(null, array('reagovat'=>null,'upravit'=>null,'forum_id'=>$forum_id), 0));
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

function forum_guest_test_start_session()
{
    static $session_start_called = false;
    if (!$session_start_called && function_exists('session_id') && session_id() == '') {
        $session_start_called = true;
        @session_start();
    }
}

function forum_guest_test_reset()
{
    forum_guest_test_start_session();
    $_SESSION['forum_guest_test'] = forum_guest_test_generate();
    return $_SESSION['forum_guest_test'];
}

function forum_guest_test_passed()
{
    forum_guest_test_start_session();
    if (isset($_SESSION['forum_guest_test_passed']) && $_SESSION['forum_guest_test_passed']) {
        return true;
    }
    if (isset($_COOKIE['fo_forum'])
        && is_array($_COOKIE['fo_forum'])
        && isset($_COOKIE['fo_forum']['guest_test_passed'])
        && $_COOKIE['fo_forum']['guest_test_passed'] == forum_guest_test_cookie_value()
    ) {
        $_SESSION['forum_guest_test_passed'] = true;
        return true;
    }
    if (defined('GUEST_TEST_TEXT')
        && isset($_COOKIE['fo_forum'])
        && is_array($_COOKIE['fo_forum'])
        && isset($_COOKIE['fo_forum']['test'])
        && strtolower($_COOKIE['fo_forum']['test']) == GUEST_TEST_TEXT
    ) {
        $_SESSION['forum_guest_test_passed'] = true;
        return true;
    }
    return false;
}

function forum_guest_test_remember_passed()
{
    forum_guest_test_start_session();
    $_SESSION['forum_guest_test_passed'] = true;
    setcookie('fo_forum[guest_test_passed]', forum_guest_test_cookie_value(), forum_cookie_expiration());
}

function forum_guest_test_cookie_value()
{
    $server_name = defined('SERVER_NAME') ? SERVER_NAME : '';
    return sha1('forum_guest_test_passed|' . $server_name);
}

function forum_cookie_expiration()
{
    return time()+(60*60*24*180);
}

function forum_guest_test_get()
{
    forum_guest_test_start_session();
    if (!isset($_SESSION['forum_guest_test'])
        || !is_array($_SESSION['forum_guest_test'])
        || !isset($_SESSION['forum_guest_test']['question'])
        || !isset($_SESSION['forum_guest_test']['answer'])
    ) {
        return forum_guest_test_reset();
    }
    return $_SESSION['forum_guest_test'];
}

function forum_guest_test_check($answer)
{
    forum_guest_test_start_session();
    if (isset($_SESSION['id']) || forum_guest_test_passed()) {
        return true;
    }

    if (!isset($_SESSION['forum_guest_test'])
        || !is_array($_SESSION['forum_guest_test'])
        || !isset($_SESSION['forum_guest_test']['answer'])
    ) {
        forum_guest_test_reset();
        return false;
    }

    $answer = forum_guest_test_normalize_answer($answer);
    $test = $_SESSION['forum_guest_test'];
    if ($answer !== '' && abs(floatval($answer) - floatval($test['answer'])) < 0.000001) {
        forum_guest_test_remember_passed();
        return true;
    }
    return false;
}

function forum_guest_test_normalize_answer($answer)
{
    $answer = str_replace(',', '.', trim((string) $answer));
    if (preg_match('/^-?[0-9]+(\.[0-9]+)?/', $answer, $matches)) {
        return $matches[0];
    }
    return '';
}

function forum_guest_test_generate()
{
    switch (mt_rand(1, 5)) {
        case 1:
            $mass = mt_rand(2, 9);
            $acceleration = mt_rand(2, 8);
            return array(
                'question' => 'Těleso o hmotnosti ' . $mass . ' kg má zrychlení ' . $acceleration . ' m/s². Jaká síla na něj působí v N?',
                'answer' => (string) ($mass * $acceleration),
            );

        case 2:
            $current = mt_rand(2, 9);
            $resistance = mt_rand(3, 12);
            return array(
                'question' => 'Rezistorem o odporu ' . $resistance . ' Ω teče proud ' . $current . ' A. Jaké je napětí ve V?',
                'answer' => (string) ($resistance * $current),
            );

        case 3:
            $speed = mt_rand(3, 12) * 10;
            $time = mt_rand(2, 5);
            return array(
                'question' => 'Auto jede rychlostí ' . $speed . ' km/h po dobu ' . $time . ' h. Jakou vzdálenost urazí v km?',
                'answer' => (string) ($speed * $time),
            );

        case 4:
            $force = mt_rand(4, 12);
            $distance = mt_rand(2, 9);
            return array(
                'question' => 'Síla ' . $force . ' N posune těleso po dráze ' . $distance . ' m. Jakou práci vykoná v J?',
                'answer' => (string) ($force * $distance),
            );

        default:
            $density = mt_rand(2, 9) * 100;
            $volume = mt_rand(2, 6);
            return array(
                'question' => 'Látka má hustotu ' . $density . ' kg/m³ a objem ' . $volume . ' m³. Jaká je její hmotnost v kg?',
                'answer' => (string) ($density * $volume),
            );
    }
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
 * Uloží do tabulky fykos.users čas poslední návštěvy fóra
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
            <p><span class="large">' . htmlspecialchars($row['title'], ENT_NOQOUTES | ENT_XHTML) . '</span><br />' . nahrad_smajliky($row["text"]) . '</p>
        </div>

    </div>

</div>
';

    return $s;      
}

/**
*    Smaže příspěvek i všechny potomky
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
*    Změní news_id u celeho vlakna pripojeneho k $forum_id
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
*    Zjistí, zda je příspěvěk kořenový, tedy jestli reakcena_id == 0
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
 * Zjistí, zda má přihlášený uživatel právo UPDATE a DELETE na $forum_id
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
