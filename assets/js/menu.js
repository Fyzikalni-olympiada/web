/**
 * Chování hlavičky (hamburger + rozbalovací submenu, bez závislostí).
 * Sdílí ho i Osmo (make update-fo-header v mo-submit).
 */
(function () {
	'use strict';

	/* ---------- hamburger hlavního menu (malé displeje) ---------- */

	var menuTlacitko = document.getElementById('menu-tlacitko');
	var hlavniMenu = document.getElementById('main-nav');
	menuTlacitko.addEventListener('click', function () {
		var otevreno = hlavniMenu.classList.toggle('open');
		menuTlacitko.setAttribute('aria-expanded', otevreno);
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && hlavniMenu.classList.contains('open')) {
			hlavniMenu.classList.remove('open');
			menuTlacitko.setAttribute('aria-expanded', 'false');
		}
		if (e.key === 'Escape') {
			zavriSubmenu();
		}
	});

	/* ---------- zvýraznění položky na Osmo ----------
	 * Hlavička je tam statický fragment s aktivní položkou Osmo; podle
	 * skutečné adresy se aktivuje nejpřesnější odkaz (Výsledky, Komise…).
	 * Na webu FO se nic neděje — absolutní odkazy vedou na jiný host. */

	var nejdelsi = null;
	document.querySelectorAll('#main-nav a[href^="http"]').forEach(function (a) {
		if (a.host !== location.host || location.pathname.indexOf(a.pathname) !== 0) {
			return;
		}
		if (!nejdelsi || a.pathname.length > nejdelsi.pathname.length) {
			nejdelsi = a;
		}
	});
	if (nejdelsi) {
		document.querySelectorAll('#main-nav .current-tab').forEach(function (li) {
			li.classList.remove('current-tab');
		});
		nejdelsi.closest('li').classList.add('current-tab');
		nejdelsi.closest('.tabbed > li').classList.add('current-tab');
	}

	/* ---------- rozbalovací submenu ---------- */

	function zavriSubmenu() {
		document.querySelectorAll('#main-nav .open').forEach(function (li) {
			li.classList.remove('open');
		});
	}

	document.querySelectorAll('#main-nav .dropdown-toggle').forEach(function (odkaz) {
		odkaz.addEventListener('click', function (e) {
			e.preventDefault();
			var li = odkaz.parentElement;
			var otevrit = !li.classList.contains('open');
			zavriSubmenu();
			if (otevrit) {
				li.classList.add('open');
			}
		});
	});
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.dropdown-toggle')) {
			zavriSubmenu();
		}
	});
})();
