<?php
if(!defined("VALID_ACCESS"))	{
	die("Neoprávněný přístup!");
}
//	Ochrana proti neoprávněnému přístupu ke skriptům

include_once(ROOT_DIR.'functions/forum.php');

echo '
<p><strong>Diskusní fórum je uzavřeno a slouží už jen jako archiv starších příspěvků.</strong>
Máte-li dotaz nebo připomínku, napište nám na <a href="mailto:info@fyzikalniolympiada.cz">info@fyzikalniolympiada.cz</a>.</p>';

if ($route['forum_thread'] !== null) {
	/* Jedno vlákno */
	$root = forum_find_root($route['forum_thread']);
	if ($root === null) {
		echo '<p>Vlákno nenalezeno.</p>';
		return;
	}
	echo '
<p><a href="' . forum_list_url($root['who'], 1) . '">&#171; Zpět na seznam vláken</a></p>

<div id="comments">
    <div class="comment-list-wrapper">
        <ul class="comment-list">

            <li class="comment comment-parent">';
	echo forum_post_html($root);
	echo forum_replies_html($root);
	echo '
            </li>
        </ul>
    </div>
</div>';
	return;
}

/* Seznam vláken */
$kdo_forum = $route['forum_who'];
$strana = $route['forum_page'];

echo '
	<h2>Diskusní fórum pro ' . ($kdo_forum === 'ucitel' ? 'učitele' : 'studenty') . '</h2>';

$pocet_stranek = forum_pocet_stranek($kdo_forum);

echo '

<div id="comments">
    <div class="comment-list-wrapper">
';
echo forum_list_html($kdo_forum, $strana);
?>
    </div>
</div>
<div class="archive-pagination archive-pagination-bottom">

<?php
if ($strana > 1) {
	echo '<div class="left"><a href="' . forum_list_url($kdo_forum, $strana - 1) . '">&#171; Předchozí strana</a></div>&nbsp;| ';
}
for ( $i=max($strana-5,1); $i<=min($strana+5,$pocet_stranek); $i++ ) {
	if ( $i == $strana ) {
		echo $i;
	} else {
		echo '<a href="' . forum_list_url($kdo_forum, $i) . '" title="Strana ' . $i . '">' . $i . '</a>';
	}
	if ( $i < min($strana+5,$pocet_stranek) ) {
		echo "&nbsp;| ";
	}
}
if ($strana < $pocet_stranek) {
	echo '<div class="right"><a href="' . forum_list_url($kdo_forum, $strana + 1) . '">Následující strana &#187;</a></div>';
}
?>

    <div class="clearer">&nbsp;</div>

</div>
