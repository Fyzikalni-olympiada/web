<?php
require_once('init.php');

$route = parsuj();
$napln = $route['napln'];
if ($napln === 404) {
	http_response_code(404); // pro dev server; v produkci řeší hosting přes 404.html
}

/*
 * HTML KOD
 */
?>
<!doctype html>
<html lang="cs">
<head>
<meta charset="utf-8">

<meta name='robots' content='index,follow' />
<meta name='googlebot' content='index,follow,snippet,archive' />

<meta name='keywords' content='fyzika, fyzikální olympiáda, fyzikalni olympiada, soutěž, soutez' />
<meta name='category' content='physics' />

<meta name='webmaster' content='All: Jan Prachař, e-mail: webmaster@fyzikalniolympiada.cz' />
<meta name='copyright' content='&copy;2002-<?= date('Y') ?> Fyzikalní olympiáda' />
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<base href="/" />

<link rel='icon' href='/favicon.ico?v=3' sizes='48x48' />
<link rel='icon' type='image/svg+xml' href='/favicon.svg?v=3' />
<link rel='icon' type='image/png' sizes='32x32' href='/favicon-32x32.png' />
<link rel='icon' type='image/png' sizes='16x16' href='/favicon-16x16.png' />
<link rel='apple-touch-icon' sizes='180x180' href='/apple-touch-icon.png' />
<link rel='manifest' href='/site.webmanifest' />
<meta name='theme-color' content='#ffffff' />

<?php if ($napln == FILE_FORUM) : ?>
<link rel='alternate' type='application/rss+xml' title='Diskuse Fyzikální olympiády' href='/rss_forum.xml' />
<?php else: ?>
<link rel='alternate' type='application/rss+xml' title='Aktuality Fyzikální olympiády' href='/rss.xml' />
<?php endif; ?>
<link rel='home' href='/' />

<link rel='stylesheet' type='text/css' media='screen' href='<?= asset('/css/bootstrap.css') ?>' />
<link rel='stylesheet' type='text/css' media='screen,projection,tv' href='<?= asset('/css/layout.css') ?>' />
<link rel='stylesheet' type='text/css' media='all' href='<?= asset('/css/content.css') ?>' />
<link rel='stylesheet' type='text/css' media='print' href='<?= asset('/css/print.css') ?>' />
<link rel="stylesheet" type="text/css" media='screen' href="<?= asset('/css/jquery.lightbox-0.5.css') ?>" />

<script src="<?= asset('/js/jquery.js') ?>" type="text/javascript"></script>
<script src="<?= asset('/js/bootstrap.js') ?>" type="text/javascript"></script>
<script type="text/javascript">
    $(function() {
		if (window.location.hash) {
			$('h3.accordion' + window.location.hash).addClass('accordion-main');
		}

		var $panels = $('h3.accordion');
		$panels.not('.accordion-main').addClass('off').next().hide();
		$panels.click(function () {
			if ($(this).is('.off')) {
				$(this).toggleClass('off').next().slideToggle();
			} else {
				var $that = $(this);
				$(this).next().slideToggle(function () {
					$that.addClass('off');
				});
			}
		});
    });
</script>

<script>
  (function() {
    var cx = '010018104406474741832:8azmff_xpkm';
    var gcse = document.createElement('script');
    gcse.type = 'text/javascript';
    gcse.async = true;
    gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
        '//www.google.com/cse/cse.js?cx=' + cx;
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(gcse, s);
  })();
</script>

<title>
Fyzikální olympiáda – <?= $route['nadpis'] ?>
</title>
</head>
<body id="fo-cuni-cz">
<div class="container">
    <div id="header" class="row">
		<form class="col-xs-6 col-sm-4 col-md-3" id="search" role="search" action="/vyhledavani">
		<label class="sr-only" for="q">Vyhledávání</label>
		  <div class="input-group">
			<input class="form-control" placeholder="Vyhledávání" type="text" name="q" id="q" autocomplete="off" />
			<span class="input-group-btn">
			<button class="btn btn-default" type="submit">
				<svg class="ikona-lupa" viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.3 15.3 21 21"/></svg>
			</button>
			</span>
		  </div>
		</form>

		<div class="col-xs-12 col-lg-5" id="logo">
			<a href="/"><img src="/pic/logo-fo.svg" alt="" /> </a>
			<a href="/"><span>FYZIKÁLNÍ OLYMPIÁDA</span></a>
			<span class="rocnik"><b><?php echo AKTUALNI_ROCNIK ?>.&nbsp;ročník</b> &middot; <?php echo AKTUALNI_ROK ?></span>
			<button type="button" id="menu-tlacitko" aria-label="Menu" aria-expanded="false"></button>
		</div>

		<div class="col-xs-12 col-lg-7 navigation" id="main-nav">
			<?php echo menu($route['pathname']); ?>
		</div>

		<div class="clearfix"></div>
    </div>

	<hr>

    <div id="main-two-columns" class="row">
        <div id="main-content" class="col-xs-12">
			<?php if ($napln === 404): ?>
			<h1 style="text-align: center">Tato stránka neexistuje</h1>
			<?php else: ?>
            <?php include(ROOT_DIR . $napln); ?>
			<?php endif; ?>
        </div>

        <div class="clearer">&nbsp;</div>
    </div>

    <div id="partneri">
        <div class="nadpis">Partneři soutěže a&nbsp;sponzoři</div>
        <ul>
        <li>
        <a href="https://www.conatex.cz/" title="Conatex – vše pro přírodní vědy">
        <img src="/pic/logoConatexCZ.svg" />
        </a>
        <li>
        <a href="http://fykos.cz/" title="Fyzikální korespondenční seminář FYKOS">
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
        viewBox="0 0 962.02 250" style="enable-background:new 0 0 962.02 250;" xml:space="preserve">
        <style type="text/css">
        .st0{fill:#1175DA;}
        .st1{fill:#FFFFFF;}
        </style>
        <g>
        <path d="M371.54,110.18h48.84V139h-48.84v59.18H335.9V51.44h84.48v28.82h-48.84V110.18z"/>
        <path d="M512.56,198.18h-35.64V133.5l-48.18-82.06h42.46l23.76,45.1l23.32-45.1h41.8l-47.52,82.06V198.18z"/>
        <path d="M606.72,198.18h-35.64V51.44h35.64v62.04l34.32-62.04h40.92l-40.92,69.74l40.92,77h-40.92l-34.32-64.24V198.18z"/>
        <path d="M842.56,125.14c0,42.02-34.32,75.9-76.78,75.9c-43.78,0-78.54-33.44-78.54-75.68c0-21.34,8.58-41.14,24.64-56.32
        c14.08-13.2,33.22-20.46,54.78-20.46C810,48.58,842.56,81.36,842.56,125.14z M723.54,124.92c0,24.2,18.26,43.12,41.58,43.12
        c22.88,0,41.36-18.92,41.36-42.46c0-25.08-17.6-44-41.14-44C741.36,81.58,723.54,100.06,723.54,124.92z"/>
        <path d="M892.27,153.74c-0.22,0.88-0.22,1.98-0.22,2.42c0,7.26,7.92,13.42,17.38,13.42c8.58,0,15.18-5.06,15.18-11.66
        c0-6.82-3.96-9.9-21.12-16.06c-32.12-11.44-44.66-24.86-44.66-47.3c0-27.5,20.24-45.98,50.82-45.98c19.8,0,36.08,8.36,44.66,22.66
        c4.18,6.82,6.16,13.2,7.7,23.54h-36.3c-1.1-10.12-6.16-14.74-16.28-14.74c-8.8,0-14.52,4.62-14.52,11.66c0,4.4,2.2,7.92,6.82,10.56
        c2.64,1.76,5.5,3.08,16.72,7.26c16.72,6.38,24.64,10.56,31.24,16.5c8.14,7.48,12.32,17.6,12.32,29.48
        c0,27.28-21.12,45.54-52.58,45.54c-32.12,0-52.14-18.26-52.14-47.3H892.27z"/>
        </g>
        <rect id="Box_00000083056659096386301490000016113851017412428735_" class="st0" width="250" height="250"/>
        <polygon class="st1" points="143.43,95.17 166.54,106.36 162.85,121.54 141.31,136.81 138.32,116.48 128.28,81.22 91.31,54.88
        109.67,92.54 107.01,110.9 140.38,155.77 107.61,162.89 130.85,152.17 102.8,109.36 89.59,95.2 28.9,64.92 68.14,110.47
        92.72,168.12 83.41,183.15 48.74,185.84 80.38,195.12 103.65,182.68 147.98,165.02 159.65,144.67 174.86,122.57 209.49,95.22
        188.16,103.95 221.1,68.78 182.84,97.43 "/>
        </svg>
        </a>
        </li>
        <li>
        Skupina ČEZ
        <img src="/pic/logo_CEZ.png" alt="Skupina ČEZ" />
        </li>
        <li><i>Mediální partner</i>
        <a href="https://ccf.fzu.cz/">
        <img src="/pic/logo_ccf.png" alt="Československý časopis pro fyziku" />
        </a>
        </li>
        </ul>
    </div>

    <div id="footer" class="row">
        <div class="col-sm-6 col-sm-push-6 col-lg-5 col-lg-offset-1">
            <p class="large pull-right text-right">
                <a href="/rss.xml" title="RSS kanál"><abbr title="Realy Simple Syndication - Odebírat novinky ve formátu RSS">RSS</abbr></a> <span class="text-separator">|</span>
                <a href="<?php echo odkaz('cojefo.html') ?>">Co je FO?</a> <span class="text-separator">|</span>
                <a href="/dokumenty/organizacni-rad-fo.pdf">Organizační řád FO</a>&nbsp;(<code>pdf</code>) <span class="text-separator">|</span>
                <a href="/dokumenty/pravidla-pro-urcovani-poradi.pdf">Pravidla pro určování pořadí</a>&nbsp;(<code>pdf</code>) <span class="text-separator">|</span>
                <a href="<?php echo odkaz('koresem.html') ?>">Korespondenční semináře</a> <span class="text-separator">|</span>
                <a href="<?php echo odkaz('jine_olym.html') ?>">Další předmětové olympiády</a> <span class="text-separator">|</span>
				<a href="<?php echo odkaz('nadace.html') ?>">Praemium Bohemiae</a>
                <!--a href="<?php echo odkaz('odkazy.html') ?>">Odkazy</a-->
			</p>
            <p class="pull-right">
                <a href="http://www.facebook.com/fyzikalniolympiada" title="Fyzikální olympiáda na Facebooku"><img class="fb" src="/pic/ico-facebook.png" alt="Fyzikální olympiáda na Facebooku"/></a>
			</p>
			<div class="clearfix"></div>
        </div>
        <div class="col-sm-6 col-sm-pull-6">
            <img src="pic/logo-fo.svg" alt="" class="left logo" />
            <p>&copy; 2002&ndash;<?= date('Y') ?> Fyzikální olympiáda. All rights Reserved.<br />
                Pokud není uvedeno jinak, podléhá text na těchto stránkách licenci <a rel="license" href="http://creativecommons.org/licenses/by/3.0/cz/">Creative Commons Uveďte autora 3.0 Česká republika</a>
                <!--a rel="license" href="http://creativecommons.org/licenses/by/3.0/cz/"><img alt="Licence Creative Commons" style="border-width:0" src="http://i.creativecommons.org/l/by/3.0/80x15.png" /></a-->
            </p>
            <p><a href="mailto:webmaster@fyzikalniolympiada.cz" title="Kontaktní email">webmaster@fyzikalniolympiada.cz</a> &ndash; Jan Prachař</p>
            <p class="quiet"><a href="http://templates.arcsin.se/">Website template</a> by <a href="http://arcsin.se/">Arcsin</a></p>
            <div class="clearer">&nbsp;</div>
        </div>
    </div>
</div>
<script src="<?= asset('/js/fo.js') ?>" type="text/javascript"></script>
</body>
</html>
