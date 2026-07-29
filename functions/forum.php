<?php
if(!defined("VALID_ACCESS"))    {die("Neoprávněný přístup!");}
//      Ochrana proti neoprávněnému přístupu ke skriptům

/**
 * Zmrazený archiv diskusního fóra, čte data/forum.yaml.
 * Seznam vláken: /diskuse[/N], /diskuse-ucitele[/N]; vlákno: /diskuse/vlakno/<id>.
 */



/** Kořenové příspěvky pro danou skupinu, od nejnovějšího (shodná sémantika s WHERE who="...") */
function forum_roots($who)
{
    $roots = array_values(array_filter(data_forum(), function ($node) use ($who) {
        return $node['who'] === $who && (!isset($node['lang']) || $node['lang'] === 'cz');
    }));
    usort($roots, function ($a, $b) {
        return strcmp($b['posted'], $a['posted']);
    });
    return $roots;
}



/** Najde kořenový příspěvek vlákna obsahujícího příspěvek $id */
function forum_find_root($id)
{
    $obsahuje = function ($node) use ($id, &$obsahuje) {
        if ($node['id'] == $id) {
            return true;
        }
        foreach (isset($node['children']) ? $node['children'] : [] as $child) {
            if ($obsahuje($child)) {
                return true;
            }
        }
        return false;
    };
    foreach (data_forum() as $root) {
        if ($obsahuje($root)) {
            return $root;
        }
    }
    return null;
}



function forum_datum_cas($posted)
{
    $monthz = array(1 => 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince');
    $ts = strtotime($posted);
    return (int) date('j', $ts) . '. ' . $monthz[(int) date('n', $ts)] . date(' Y, G:i', $ts);
}



function forum_post_html($node, $root_id)
{
    $s = '';
    if (isset($node['email']) && preg_match("/@fykos.mff.cuni.cz$/", $node['email'])) {
        $extra_dt = ' class="org"';
    } else {
        $extra_dt = '';
    }
    if (isset($node['email'])) {
        $email = str_replace("@", "(zavinac)", $node['email']);
        $s_email = '<a href="e-mail:' . $email . '" title="' . lng('Autor příspěvku', 'Author of the post') . '">' . $node['author'] . '</a>';
    } else {
        $s_email = $node['author'];
    }

    $s .= '
<div class="comment-content" id="p' . $node['id'] . '"' . $extra_dt . '>

    <div class="comment-body">

        <div class="post-date">

            <div class="left"><img src="images/sample-gravatar.gif" height="14" width="14" alt="" />
                <span class="loud">' . $s_email . '</span>
                &ndash; ' . forum_datum_cas($node['posted']) . '</div>
';
    if ($GLOBALS['forum_thread'] === null) {
        $s .= '
            <div class="right"><a href="' . ROOT_WWW . 'diskuse/vlakno/' . $root_id . '#p' . $node['id'] . '">#' . $node['id'] . '</a></div>';
    }
    $s .= '
            <div class="clearer">&nbsp;</div>

        </div>

        <div class="comment-text">
            <p><span class="large">' . htmlspecialchars(isset($node['title']) ? $node['title'] : '', ENT_NOQUOTES | ENT_XHTML) . '</span><br />' . $node['text'] . '</p>
        </div>

    </div>

</div>
';

    return $s;
}



/** Vnořené odpovědi (rekurzivně) */
function forum_replies_html($node, $root_id)
{
    if (empty($node['children'])) {
        return '';
    }
    $s = '
<ul class="children">';
    foreach ($node['children'] as $child) {
        $s .= '
    <li class="comment">';
        $s .= forum_post_html($child, $root_id);
        $s .= forum_replies_html($child, $root_id);
        $s .= '
    </li>';
    }
    $s .= '
</ul>';
    return $s;
}



/** Jedna stránka seznamu vláken (vlákna rozbalená jako dřív) */
function forum_list_html($who, $strana)
{
    $roots = forum_roots($who);
    $zacatek = ($strana - 1) * FORUM_VLAKEN_NA_STRANKU;

    $s = '
<ul class="comment-list">';
    foreach (array_slice($roots, $zacatek, FORUM_VLAKEN_NA_STRANKU) as $root) {
        $s .= '
    <li class="comment comment-parent">';
        $s .= forum_post_html($root, $root['id']);
        $s .= forum_replies_html($root, $root['id']);
        $s .= '
    </li>';
    }
    $s .= '
</ul>';
    return $s;
}



function forum_pocet_stranek($who)
{
    return max(1, (int) ceil(count(forum_roots($who)) / FORUM_VLAKEN_NA_STRANKU));
}



/** Cesta na stránku seznamu */
function forum_list_url($who, $strana)
{
    $base = ROOT_WWW . ($who === 'ucitel' ? 'diskuse-ucitele' : 'diskuse');
    return $strana > 1 ? $base . '/' . $strana : $base;
}
