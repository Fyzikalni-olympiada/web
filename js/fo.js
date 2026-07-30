/**
 * Klientský JS statického webu FO:
 *  - přesměrování starých adres s query parametry
 *  - zobrazit / skrýt boční panel na malých displejích
 *  - boční panel „Nejbližší termíny" z /data/terms.json
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

	/* ---------- zobrazit / skrýt boční panel (malé displeje) ---------- */

	var sidebar = document.getElementById('sidebar');
	var zaclona = document.createElement('div');
	zaclona.id = 'sidebar-zaclona';
	document.body.appendChild(zaclona);

	function sidebarPrepni(otevrit) {
		sidebar.classList.toggle('open', otevrit);
		zaclona.classList.toggle('open', otevrit);
	}
	document.querySelectorAll('[data-toggle=sidebar]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			sidebarPrepni(!sidebar.classList.contains('open'));
		});
	});
	zaclona.addEventListener('click', function () {
		sidebarPrepni(false);
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			sidebarPrepni(false);
		}
	});
	/* swipe doprava panel zavře */
	var swipeX = null;
	sidebar.addEventListener('touchstart', function (e) {
		swipeX = e.touches[0].clientX;
	}, { passive: true });
	sidebar.addEventListener('touchend', function (e) {
		if (swipeX !== null && e.changedTouches[0].clientX - swipeX > 60) {
			sidebarPrepni(false);
		}
		swipeX = null;
	}, { passive: true });

	/* ---------- pomocné ---------- */

	function dnes() {
		var d = new Date();
		return d.getFullYear() + '-'
			+ String(d.getMonth() + 1).padStart(2, '0') + '-'
			+ String(d.getDate()).padStart(2, '0');
	}

	/* ---------- nejbližší termíny (boční panel) ---------- */

	var terminyBox = document.getElementById('nejblizsi-terminy');
	if (terminyBox) {
		fetch('/data/terms.json').then(function (r) { return r.json(); }).then(function (terminy) {
			var skupiny = [
				['Kategorie A (4.&nbsp;ročník SŠ)', ['spolecne', 'A']],
				['Kategorie B&ndash;D (1.&ndash;3.&nbsp;ročník SŠ)', ['spolecne', 'BCD']],
				['Kategorie E, F (8. a&nbsp;9. třída ZŠ)', ['spolecne', 'EF']],
				['Archimediáda (7.&nbsp;třída ZŠ)', ['G']]
			];
			var od = dnes();
			var html = '';
			skupiny.forEach(function (skupina) {
				var nejblizsi = null;
				terminy.forEach(function (t) {
					if (skupina[1].indexOf(t.kategorie) === -1 || !t.date || t.date < od) {
						return;
					}
					if (nejblizsi === null || t.date < nejblizsi.date) {
						nejblizsi = t;
					}
				});
				if (nejblizsi !== null) {
					html += '<h6>' + skupina[0] + '</h6>'
						+ '<div class="left">' + nejblizsi.nazev + '</div>'
						+ '<div class="right">' + nejblizsi.termin + '</div>'
						+ '<div class="clearer">&nbsp;</div>';
				}
			});
			terminyBox.innerHTML = html;
		});
	}

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
