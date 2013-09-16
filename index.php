<?php
define("VALID_ACCESS", 1);
ob_start();
require_once('init.php');

/*
 * HTML KOD
 */
echo '<?xml version="1.0" encoding="utf-8"?>
';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='cs' lang='cs'>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv='Content-language' content='cs' />

<meta name='robots' content='index,follow' />
<meta name='googlebot' content='index,follow,snippet,archive' />

<meta name='description' content='Fyzikální olympiáda, oficiální stránky' />
<meta name='keywords' content='fyzika, fyzikální olympiáda, fyzikalni olympiada, soutěž, soutez' />
<meta name='category' content='physics' />

<meta name='author' content='All: Jan Prachař, e-mail: jan.prachar@fyzikalniolympiada.cz' />
<meta name='webmaster' content='All: Jan Prachař, e-mail: webmaster@fyzikalniolympiada.cz' />
<meta name='copyright' content='&copy;2004-2011 Jan Prachař, e-mail: jan.prachar@fyzikalniolympiada.cz' />
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<base href="http://<?= SERVER_NAME ?>/" />

<link rel='shortcut icon' type='image/x-icon' href='favicon.ico' />

<? if ($napln == FILE_FORUM) : ?>
<link rel='alternate' type='application/rss+xml' title='Diskuse Fyzikální olympiády' href='http://<?= SERVER_NAME ?>/rss_forum.php' />
<? else: ?>
<link rel='alternate' type='application/rss+xml' title='Aktuality Fyzikální olympiády' href='http://<?= SERVER_NAME ?>/rss.php' />
<? endif; ?>
<link rel='home' href='http://<?= SERVER_NAME ?>/' />

<link rel='stylesheet' type='text/css' media='screen' href='/css/bootstrap.css' />
<link rel='stylesheet' type='text/css' media='screen,projection,tv' href='/css/layout.css' />
<link rel='stylesheet' type='text/css' media='all' href='/css/content.css' />
<link rel='stylesheet' type='text/css' media='print' href='/css/print.css' />
<link rel="stylesheet" type="text/css" media='screen' href="/css/jquery.lightbox-0.5.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/prettyPhoto.css" title="prettyPhoto main stylesheet" charset="utf-8" />
<!--[if lte IE 8]><link rel="stylesheet" type="text/css" href="/css/ie8.css" /><![endif]-->

<script src="js/jquery.js" type="text/javascript"></script>
<script src="js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script src="js/bootstrap.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function() {
		$('[data-toggle=offcanvas]').click(function() {
			$('.row-offcanvas').toggleClass('active');
			$('.sidebar-offcanvas').toggleClass('active');
		});

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
		$('#sidebar .section-title-image a').attr('rel', 'gallery[]').prettyPhoto({
			social_tools: false
		});
    });
</script>

<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load('search', '1');
  google.setOnLoadCallback(function() {
    google.search.CustomSearchControl.attachAutoCompletion(
      '002398901476820886408:ooqo1rb0p1a',
      document.getElementById('q'),
      'cse-search-box');
  });
</script>

<title>
FYZIKÁLNÍ OLYMPIÁDA<?php echo ' :: ' . nadpis(); ?>
</title>
</head>
<body id="fo-cuni-cz">
<div class="container">
    <div id="header" class="row">
		<div class="col-xs-12 col-md-5 col-lg-4" id="logo">
			<a href="/"><img src="images/logo.gif" alt="" /> </a>
			<a href="/"><span>FYZIKÁLNÍ OLYMPIÁDA</span></a>
		</div>

		<div class="col-xs-12 col-md-7 navigation" id="main-nav">
			<?php echo menu(); ?>
		</div>

		<div class="clearfix"></div>

		<div class="col-xs-12 navigation">
			<div id="sub-nav">
            <?php echo submenu($parentID); ?>
  			</div>
        </div>
    </div>

    <div id="main-two-columns" class="row row-offcanvas row-offcanvas-right">
        <div id="main-content" class="col-lg-9 col-md-8 col-xs-12">
			<div class="pull-right visible-sm visible-xs">
				<button type="button" class="btn-xs btn-sm" data-toggle="offcanvas">Zobrazit boční panel</button>
			</div>
       
            <h1><?php echo nadpis(); ?></h1>
            <?php
                if ($napln == FILE_NEWS) {
                    include(ROOT_DIR.FILE_NEWS);
                } else {
                    include(ROOT_DIR.$napln);
                }
            ?>
        </div>

		<div id="sidebar" class="col-lg-3 col-md-4 col-sm-6 sidebar-offcanvas">
            <form action="/vyhledavani" id="cse-search-box">
              <div>
                <input type="hidden" name="cx" value="002398901476820886408:ooqo1rb0p1a" />
                <input type="hidden" name="cof" value="FORID:11" />
                <input type="hidden" name="ie" value="utf-8" />
                <input class="text" type="text" name="q" id="q" autocomplete="off" size="31" />
                <input class="button" type="submit" name="sa" value="Hledat" />
              </div>
            </form>
            <script type="text/javascript" src="http://www.google.com/cse/brand?form=cse-search-box&lang=cs"></script>

            <div class="section">
                <div class="section-title">Právě probíhá</div>
                <div class="section-content">
                    <?php echo AKTUALNI_ROCNIK ?>.&nbsp;ročník &mdash; <?php echo AKTUALNI_ROK?>
                </div>
            </div>

            <div class="section">
                <div class="section-title section-title-image">
                    <?php echo rand_thumb(); ?>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Nejbližští termíny</div>
                <div class="section-content">
                    <?php foreach (latest_terms() as $title=>$term) : ?>
                        <h6><?php echo $title; ?></h6>
                        <?php echo $term; ?>
                        <div class="clearer">&nbsp;</div>
                    <?php endforeach;?>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Novinky</div>
                <div class="section-content">
                       <?php echo novinky(); ?>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Ústřední komise</div>
                <div class="section-content">
                    <ul class="nice-list">
                        <li><a href="mailto:ivo.volf@uhk.cz">prof. Ivo Volf</a> <span class="quiet">&ndash; předseda</span></li>
                        <li><a href="mailto:bohumil.vybiral@uhk.cz">prof. Bohumil Vybíral</a> <span class="quiet">&ndash; místopředseda</span></li>
                        <li><a href="mailto:jan.kriz@uhk.cz">dr. Jan Kříž</a> <span class="quiet">&ndash; místopředseda</span></li>
                        <li><a href="mailto:premysls(z@vinac)seznam.cz">dr. Přemysl Šedivý</a> <span class="quiet">&ndash; redakce úloh a tisků</span></li>
                        <li><a href="mailto:jaresova.miroslava(z@vinac)sspst-chrudim.cz">dr. Miroslava Jarešová</a> <span class="quiet">&ndash; členka předsednictva</span></li>
                    </ul>
                    <ul class="nice-list">
                        <li><a href="http://www.uhk.cz/fo" title="Centrum péče o fyzikální talenty na UHK">Centrum péče o fyzikální talenty</a></li>
                        <li><a href="http://cental.uhk.cz/" title="Centrum talentů M&amp;F&amp;I">CenTal &ndash; Centrum talentů M&amp;F&amp;I</a></li>
                    </ul>
                    
                </div>
            </div>

            <div class="section">
                <div class="section-title">Krajské stránky</div>
                <div class="section-content">
					<div class="visible-md visible-lg map-container">
						<?php include 'html/stranky_regionu.html' ?>
					</div>	
                    <ul class="nice-list visible-sm visible-xs">
                        <li><a href="http://praha.fyzikalniolympiada.cz/" title="Pražské stránky">Praha</a></li>
                        <li><a href="http://www.pf.jcu.cz/structure/departments/kaft/pro-verejnost/fyzikalni-olympiada/" title="Stránky jihočeského kraje">Jihočeský kraj</a></li>
                        <li><a href="http://www.jaroska.cz/fo/" title="Stránky jihomoravského kraje">Jihomoravský kraj</a></li>
                        <li><a href="http://kvary.fyzikalniolympiada.cz/" title="Stránky karlovarského kraje">Karlovarský kraj</a></li>
                        <li><a href="http://www.viki.sro.cz/obj/fo/" title="Stránky kraje Vysočina">Kraj Vysočina</a></li>
                        <li><a href="http://www.gybon.cz/~sada/fyzikalniolympiada.html" title="Stránky královéhradeckého kraje">Královéhradecký kraj</a></li>
                        <li><a href="http://www.sportgym.cz/aktivity/fyzikalni-olympiada" title="Stránky libereckého kraje">Liberecký kraj</a></li>
                        <li><a href="http://www.svcoo.cz/souteze/souteze_fyzika.html" title="Stránky moravskoslezského kraje">Moravskoslezský kraj</a></li>
                        <li><a href="http://www.ktf.upol.cz/fo/" title="Stránky olomouckého kraje">Olomoucký kraj</a></li>
                        <li><a href="http://www.gypce.cz/fyzikalni-olympiada-pardubickeho-kraje-2/" title="Stránky pardubického kraje">Pardubický kraj</a></li>
                        <li><a href="http://kof.zcu.cz/fo/" title="Stránky plzeňského kraje">Plzeňský kraj</a></li>
                        <li><a href="http://fo.czechian.net/" title="Stránky středočeského kraje">Středočeský kraj</a></li>
                        <li><a href="http://physics.ujep.cz/~fo/" title="Stránky ústeckého kraje">Ústecký kraj</a></li>
                        <li><a href="http://rvfo.webz.cz/" title="Stránky zlínského kraje">Zlínský kraj</a></li>
                    </ul>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Partneři soutěže a sponzoři</div>
                <div class="section-content">
					<div style="margin: 0 0 20px;">
						<a href="<?php echo odkaz('nadace.html') ?>">Praemium Bohemiae</a>
					</div>
                    <div style="margin: 0 0 20px;">
                       <img src="/pic/logo_CEZ.png" alt="Skupina ČEZ" style="width: 99px; height: 99px; float: right;" />
                       Skupina ČEZ
                       <br style="clear: right" />
                    </div>
                    <div>
                       <i>Mediální partner</i>
                       <a href="http://www.cscasfyz.fzu.cz/">
                        <img src="/pic/logo_ccf.png" alt="Československý časopis pro fyziku" style="width: 220px; height: 52px; float: right; margin-top: 1em;" />
                       </a>
                       <br style="clear: right" />
                    </div>
                </div>
            </div>
        </div>
        <div class="clearer">&nbsp;</div>
    </div>

    <div id="footer" class="row row-offcanvas row-offcanvas-right">
        <div class="col-sm-6 col-sm-push-6 col-lg-5 col-lg-offset-1">
            <p class="large pull-right text-right">
                <a href="/rss" title="RSS kanál"><abbr title="Realy Simple Syndication - Odebírat novinky ve formátu RSS">RSS</abbr></a> <span class="text-separator">|</span>
                <a href="<?php echo odkaz('cojefo.html') ?>">Co je FO?</a> <span class="text-separator">|</span>
                <a href="<?php echo ROOT_WWW ?>dokumenty/organizacni-rad-fo.pdf">Organizační řád FO</a> (<code>pdf</code>) <span class="text-separator">|</span>
                <a href="<?php echo odkaz('terminy.html') ?>">Termíny</a> <span class="text-separator">|</span>
                <a href="<?php echo odkaz('jine_olym.html') ?>">Další předmětové olympiády</a> <span class="text-separator">|</span>
                <a href="<?php echo odkaz('odkazy.html') ?>">Odkazy</a>
			</p>
            <p class="pull-right">
                <a href="http://www.facebook.com/fyzikalniolympiada" title="Fyzikální olympiáda na Facebooku"><img src="/pic/ico-facebook.png" alt="Fyzikální olympiáda na Facebooku"/></a>
			</p>
        </div>
        <div class="col-sm-6 col-sm-pull-6">
            <img src="images/logo-small.gif" alt="" class="left" />
            <p>&copy; 2002&ndash;<?php echo date('Y') ?> Fyzikální olympiáda. All rights Reserved.<br />
                Pokud není uvedeno jinak, podléhá text na těchto stránkách licenci <a rel="license" href="http://creativecommons.org/licenses/by/3.0/cz/">Creative Commons Uveďte autora 3.0 Česká republika</a>
                <!--a rel="license" href="http://creativecommons.org/licenses/by/3.0/cz/"><img alt="Licence Creative Commons" style="border-width:0" src="http://i.creativecommons.org/l/by/3.0/80x15.png" /></a-->
            </p>
            <p><a href="mailto:webmaster@fyzikalniolympiada.cz" title="Kontaktní email">webmaster@fyzikalniolympiada.cz</a> &ndash; Jan Prachař</p>
            <p class="quiet"><a href="http://templates.arcsin.se/">Website template</a> by <a href="http://arcsin.se/">Arcsin</a></p>
            <div class="clearer">&nbsp;</div>
        </div>
    </div>
</div>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-31717225-2', 'fyzikalniolympiada.cz');
  ga('send', 'pageview');
</script>
</body>
</html>
<?php
$mysql->close();
$mysql_odkazy->close();

ob_end_flush();
