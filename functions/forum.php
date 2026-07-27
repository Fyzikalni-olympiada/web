<?php
if(!defined("VALID_ACCESS"))    {die("Neoprávněný přístup!");}                         
//      Ochrana proti neoprávněnému přístupu ke skriptům

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
                &ndash; ' . $row['datum_cas'] . '</div>
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
