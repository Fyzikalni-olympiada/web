/**
 * Klientský JS statického webu FO:
 *  - přesměrování starých adres s query parametry
 *  - hamburger menu na malých displejích
 *  - zvýraznění nejbližšího kroku v „Jak se zapojit" na homepage
 *  - zvýraznění nejbližšího termínu na /terminy (podle data-date)
 *  - náhodná fotka v bočním panelu z /data/thumbs.json
 */
(function () {
	'use strict';

	/* ---------- přesměrování starých adres ---------- */

	var params = new URLSearchParams(location.search);
	var path = location.pathname.replace(/\.html$/, '').replace(/\/$/, '') || '/';

	if (path === '/novinka' && params.get('id')) {
		location.replace('/novinka/' + encodeURIComponent(params.get('id')));
		return;
	}
	if (path === '/diskuse' || path === '/diskuse-ucitele') {
		if (params.get('forum_id')) {
			location.replace('/diskuse/vlakno/' + encodeURIComponent(params.get('forum_id')));
			return;
		}
		var base = params.get('who') === 'ucitel' || path === '/diskuse-ucitele'
			? '/diskuse-ucitele' : '/diskuse';
		var page = parseInt(params.get('page'), 10);
		if (base !== path || page > 1) {
			location.replace(base + (page > 1 ? '/' + page : ''));
			return;
		}
	}
	if (path === '/' && parseInt(params.get('page'), 10) > 1) {
		location.replace('/archiv-novinek');
		return;
	}

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
	});


	/* ---------- pomocné ---------- */

	function dnes() {
		var d = new Date();
		return d.getFullYear() + '-'
			+ String(d.getMonth() + 1).padStart(2, '0') + '-'
			+ String(d.getDate()).padStart(2, '0');
	}

	/* ---------- nejbližší krok v „Jak se zapojit" (homepage) ---------- */

	document.querySelectorAll('#zapojeni .cesta').forEach(function (cesta) {
		var pristi = null;
		cesta.querySelectorAll('.kdy [data-date]').forEach(function (el) {
			var date = el.getAttribute('data-date');
			if (date >= dnes() && (pristi === null || date < pristi.getAttribute('data-date'))) {
				pristi = el;
			}
		});
		if (pristi === null) {
			return;
		}
		cesta.querySelectorAll('.krok').forEach(function (k) {
			k.classList.remove('aktualni');
		});
		pristi.classList.add('pristi');
		var krok = pristi.closest('.krok');
		krok.classList.add('nadchazi');
		var stitek = document.createElement('div');
		stitek.className = 'stitek';
		stitek.textContent = 'nejblíž';
		krok.insertBefore(stitek, krok.firstChild);
	});

	/* ---------- zvýraznění nejbližšího termínu na /terminy ---------- */

	document.querySelectorAll('.terminy').forEach(function (blok) {
		var radky = blok.querySelectorAll('[data-date]');
		for (var i = 0; i < radky.length; i++) {
			var date = radky[i].getAttribute('data-date');
			if (date && date >= dnes()) { // stejná tolerance jako staré date+1 den > teď
				radky[i].classList.add('aktualni');
				break;
			}
		}
	});

	/* ---------- náhodná fotka ---------- */

	var fotkaBox = document.getElementById('nahodna-fotka');
	if (fotkaBox) {
		fetch('/data/thumbs.json').then(function (r) { return r.json(); }).then(function (fotky) {
			if (!fotky.length) {
				return;
			}
			var html = '';
			for (var i = 0; i < 5 && fotky.length; i++) {
				var f = fotky.splice(Math.floor(Math.random() * fotky.length), 1)[0];
				html += '<a href="' + f.full + '"' + (i === 0 ? '' : ' style="display: none;"')
					+ '><img src="' + f.thumb + '" alt="Ze života Fyzikální olympiády"/></a>';
			}
			fotkaBox.innerHTML = html;
			if (window.jQuery && jQuery.fn.prettyPhoto) {
				jQuery('#sidebar .section-title-image a').attr('rel', 'gallery[]')
					.prettyPhoto({ social_tools: false });
			}
		});
	}
})();
