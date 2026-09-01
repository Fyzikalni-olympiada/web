/**
 * Klientský JS statického webu FO (bez závislostí; menu má vlastní menu.js):
 *  - přesměrování starých adres s query parametry
 *  - taby, accordion, fotogalerie (dřív bootstrap.js / jquery.lightbox)
 *  - zvýraznění nejbližšího kroku na osách (homepage, /terminy)
 *  - filtr studijních textů
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

	/* ---------- taby (dřív bootstrap.js) ---------- */

	document.querySelectorAll('.nav-tabs a[data-toggle="tab"]').forEach(function (odkaz) {
		odkaz.addEventListener('click', function (e) {
			e.preventDefault();
			var pane = document.querySelector(odkaz.getAttribute('href'));
			odkaz.closest('.nav-tabs').querySelectorAll('li').forEach(function (li) {
				li.classList.toggle('active', li === odkaz.parentElement);
			});
			pane.parentElement.querySelectorAll(':scope > .tab-pane').forEach(function (p) {
				p.classList.toggle('active', p === pane);
			});
		});
	});

	/* ---------- accordion (/ustredni-kolo, dřív jQuery v hlavičce) ---------- */

	var panely = document.querySelectorAll('h3.accordion');
	if (panely.length) {
		if (location.hash) {
			var oteviranyPanel = document.querySelector('h3.accordion' + location.hash);
			if (oteviranyPanel) {
				oteviranyPanel.classList.add('accordion-main');
			}
		}
		panely.forEach(function (panel) {
			if (!panel.classList.contains('accordion-main')) {
				panel.classList.add('off');
				panel.nextElementSibling.style.display = 'none';
			}
			panel.addEventListener('click', function () {
				var skryty = panel.classList.toggle('off');
				panel.nextElementSibling.style.display = skryty ? 'none' : '';
			});
		});
	}

	/* ---------- fotogalerie: nativní dialog (dřív jquery.lightbox) ---------- */

	var galerie = document.querySelectorAll('#photoGallery .photoContainer a');
	if (galerie.length) {
		var dialog = document.createElement('dialog');
		dialog.className = 'lightbox';
		dialog.innerHTML = '<img alt=""><div class="popisek"></div>';
		document.body.appendChild(dialog);
		var obrazek = dialog.querySelector('img');
		var aktualni = 0;
		var ukaz = function (i) {
			aktualni = (i + galerie.length) % galerie.length;
			obrazek.src = galerie[aktualni].getAttribute('href');
			dialog.querySelector('.popisek').innerHTML = galerie[aktualni].getAttribute('title') || '';
		};
		galerie.forEach(function (odkaz, i) {
			odkaz.addEventListener('click', function (e) {
				e.preventDefault();
				ukaz(i);
				dialog.showModal();
			});
		});
		obrazek.addEventListener('click', function () {
			ukaz(aktualni + 1);
		});
		dialog.addEventListener('click', function (e) {
			if (e.target !== obrazek) {
				dialog.close();
			}
		});
		document.addEventListener('keydown', function (e) {
			if (!dialog.open) {
				return;
			}
			if (e.key === 'ArrowRight') {
				ukaz(aktualni + 1);
			} else if (e.key === 'ArrowLeft') {
				ukaz(aktualni - 1);
			}
		});
	}


	/* ---------- pomocné ---------- */

	function dnes() {
		var d = new Date();
		return d.getFullYear() + '-'
			+ String(d.getMonth() + 1).padStart(2, '0') + '-'
			+ String(d.getDate()).padStart(2, '0');
	}

	/* ---------- nejbližší krok na osách (homepage, /terminy) ---------- */

	document.querySelectorAll('.cesta').forEach(function (cesta) {
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

	/* ---------- aktivní položka bočního obsahu ---------- */

	var toc = document.querySelector('.obsah-toc');
	if (toc) {
		var odkazy = toc.querySelectorAll('a[href*="#"]');
		var cile = Array.prototype.map.call(odkazy, function (a) {
			return document.getElementById(a.getAttribute('href').split('#')[1]);
		});
		var oznac = function () {
			var akt = 0;
			cile.forEach(function (c, i) {
				if (c && c.getBoundingClientRect().top <= 120) {
					akt = i;
				}
			});
			if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 5) {
				akt = odkazy.length - 1;
			}
			odkazy.forEach(function (a, i) {
				a.classList.toggle('akt', i === akt);
			});
		};
		document.addEventListener('scroll', oznac, { passive: true });
		oznac();
	}

	/* ---------- filtr studijních textů podle kategorie ---------- */

	var filtr = document.querySelector('.tx-filtr');
	if (filtr) {
		filtr.addEventListener('click', function (e) {
			var volba = e.target.closest('a');
			if (!volba) {
				return;
			}
			e.preventDefault();
			filtr.querySelectorAll('a').forEach(function (a) {
				a.classList.toggle('akt', a === volba);
			});
			var kat = volba.getAttribute('data-kat');
			document.querySelectorAll('.tx-radek[data-kat]').forEach(function (r) {
				r.style.display = kat === '*' || r.getAttribute('data-kat') === kat ? '' : 'none';
			});
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
})();
