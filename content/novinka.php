<?php
$item = $route['novinka'];
if ($item === null) {
	/* sem vedou i staré odkazy /novinka?id=N – přesměruje je js/fo.js */
	echo '<p>Novinka nenalezena. <a href="/">Všechny novinky</a></p>';
	return;
}

echo novinka_html($item, false);
echo '
        <p><a href="/">&#171; Všechny novinky</a></p>';
