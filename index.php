<?php
define("VALID_ACCESS", 1);
ob_start();
require_once('init.php');

/*
 * HTML KOD
 */
echo '<?xml version="1.0" encoding="windows-1250"?>
';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='cs' lang='cs'>
<head>
<!-- ENCODING /-->
<meta http-equiv="Content-Type" content="text/html; charset=windows-1250" />
<meta http-equiv='Content-language' content='cs' />
<!-- ENCODING end /-->

<!-- ROBOTS /-->
<meta name='robots' content='index,follow' />
<meta name='googlebot' content='index,follow,snippet,archive' />
<!-- ROBOTS end /-->

<!-- KEYWORDS & CATEGORIES - but who cares now :-( /-->
<meta name='description' content='Fyzikální olympiáda, oficiální stránky' />
<meta name='keywords' content='fyzika, fyzikální olympiáda, fyzikalni olympiada, soutěž, soutez' />
<meta name='category' content='physics' />
<!-- KEYWORDS & CATEGORIES - end /-->

<!-- AUTHOR self promo - use 'crypted' e-mails defeats robotic harvesters /-->
<meta name='author' content='All: Jan Prachař, e-mail: jan.prachar@fyzikalniolympiada.cz' />
<meta name='webmaster' content='All: Jan Prachař, e-mail: webmaster@fyzikalniolympiada.cz' />
<meta name='copyright' content='&copy;2004-2011 Jan Prachař, e-mail: jan.prachar@fyzikalniolympiada.cz' />
<!-- AUTHOR self promo - end /-->

<!-- PICS label - content rating & description (kids, security...) /-->
<meta http-equiv="pics-label" content='(pics-1.1 "http://www.icra.org/ratingsv02.html" l gen true for "http://fo.cuni.cz" r (nz 1 vz 1 lz 1 oz 1 cb 1))' />
<!-- PICS label - end /-->

<!-- BROWSER SPECIFIC FEATURES = ALL OFF /-->
<!-- MSIE - 'helpful' features /-->
<meta http-equiv='imagetoolbar' content='no' />
<meta http-equiv='MSThemeCompatible' content='no' />
<meta name='MS.LOCALE' content='cs' />
<!-- OPERA - image resizing /-->
<meta name='autosize' content='off' />
<!-- BROWSER SPECIFIC FEATURES = end /-->

<base href="http://<?= SERVER_NAME ?>/" />

<!-- ICON /-->
<link rel='shortcut icon' type='image/x-icon' href='favicon.ico' />
<!-- ICON end /-->

<!-- NAVIGATION - based on logical relations of documents /-->
<!-- homepage /-->
<? if ($napln == FILE_FORUM) : ?>
<link rel='alternate' type='application/rss+xml' title='Diskuse Fyzikální olympiády' href='http://<?= SERVER_NAME ?>/rss_forum.php' />
<? else: ?>
<link rel='alternate' type='application/rss+xml' title='Aktuality Fyzikální olympiády' href='http://<?= SERVER_NAME ?>/rss.php' />
<? endif; ?>

<link rel='home' href='http://<?= SERVER_NAME ?>/index.php' />
<!-- NAVIGATION - end /-->

<!-- CASCADING STYLE SHEETS /-->
<!-- LINKED STYLE /-->
<link rel='stylesheet' type='text/css' media='screen,projection,tv' href='./css/layout.css' />
<link rel='stylesheet' type='text/css' media='all' href='./css/content.css' />
<link rel='stylesheet' type='text/css' media='print' href='./css/print.css' />
<link rel="stylesheet" type="text/css" media='screen' href="./css/jquery.lightbox-0.5.css" />
<link rel="stylesheet" type="text/css" media="screen" href="./css/prettyPhoto.css" title="prettyPhoto main stylesheet" charset="utf-8" />
<style type="text/css" media="all">
@import "css/jtip.css";
</style>
<!-- CASCADING STYLE SHEETS - end /-->

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
<script src="js/jquery.js" type="text/javascript"></script>
<script src="js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    $(function() {
        $('#sidebar .section-title-image a').attr('rel', 'gallery[]').prettyPhoto({ });
    });
</script>

<title>
FYZIKÁLNÍ OLYMPIÁDA<?php echo ' :: ' . nadpis(); ?>
</title>
</head>
<body id="fo-cuni-cz">
<div id="site-wrapper">
    <div id="header">
        <div id="top">
            <div class="left" id="logo">
                <a href="/"><img src="images/logo.gif" alt="" /> </a>
                <a href="/"><span>FYZIKÁLNÍ OLYMPIÁDA</span></a>
            </div>

            <div class="left navigation" id="main-nav">
                <?php echo menu(); ?>
                <div class="clearer">&nbsp;</div>
            </div>

            <div class="clearer">&nbsp;</div>
        </div>

        <div class="navigation" id="sub-nav">
            <?php echo submenu($parentID); ?>
            <div class="clearer">&nbsp;</div>
        </div>
    </div>

    <div class="main" id="main-two-columns">
        <div class="left" id="main-content">
            <?php if ($menu_kdo = menu_kdo()) :?>
            <div id="role">
                <h3>Vaše role</h3>
                <?php echo $menu_kdo; ?>
            </div>
            <?php endif; ?>

            <h1><?php echo nadpis(); ?></h1>
            <?php
                if ($napln == FILE_NEWS) {
                    include(ROOT_DIR.FILE_NEWS);
                } else {
                    include(ROOT_DIR.$napln);
                }
            ?>
        </div>

        <div class="right sidebar" id="sidebar">

            <form action="/vyhledavani" id="cse-search-box">
              <div>
                <input type="hidden" name="cx" value="002398901476820886408:ooqo1rb0p1a" />
                <input type="hidden" name="cof" value="FORID:11" />
                <input type="hidden" name="ie" value="windows-1250" />
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
                    <ul class="nice-list">
                        <li><a href="http://praha.fyzikalniolympiada.cz/" title="Pražské stránky">Praha</a></li>
                        <li><a href="http://www.pf.jcu.cz/katedry/fyzika/fo/" title="Stránky jihočeského kraje">Jihočeský kraj</a></li>
                        <li><a href="http://www.jaroska.cz/fo/" title="Stránky jihomoravského kraje">Jihomoravský kraj</a></li>
                        <li><a href="http://kvary.fyzikalniolympiada.cz/" title="Stránky karlovarského kraje">Karlovarský kraj</a></li>
                        <li><a href="http://www.viki.sro.cz/obj/fo/" title="Stránky kraje Vysočina">Kraj Vysočina</a></li>
                        <li><a href="http://www.gybon.cz/~sada/fyzikalniolympiada.html" title="Stránky královéhradeckého kraje">Královéhradecký kraj</a></li>
                        <li><a href="http://www.sportgym.cz/aktivity/fyzikalni-olympiada" title="Stránky libereckého kraje">Liberecký kraj</a></li>
                        <li><a href="http://www.svcoo.cz/souteze/souteze_fyzika.html" title="Stránky moravskoslezského kraje">Moravskoslezský kraj</a></li>
                        <li><a href="http://www.ktf.upol.cz/fo/" title="Stránky olomouckého kraje">Olomoucký kraj</a></li>
                        <li><a href="http://www.gypce.cz/files/podweb.php?fo" title="Stránky pardubického kraje">Pardubický kraj</a></li>
                        <li><a href="http://kof.zcu.cz/fo/" title="Stránky plzeňského kraje">Plzeňský kraj</a></li>
                        <li><a href="http://fo.czechian.net/" title="Stránky středočeského kraje">Středočeský kraj</a></li>
                        <li><a href="http://physics.ujep.cz/~fo/" title="Stránky ústeckého kraje">Ústecký kraj</a></li>
                        <li><a href="http://rvfo.webz.cz/" title="Stránky zlínského kraje">Zlínský kraj</a></li>
                    </ul>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Partner soutěže</div>
                <div class="section-content">
                       <img src="/pic/logo_CEZ.png" alt="Skupina ČEZ" style="width: 99px; height: 99px; float: right;" />
                       Skupina ČEZ
                       <br style="clear: right" />
                </div>
            </div>

            <div class="section">
                <div class="section-title">Návštěvnost</div>
                <div class="section-content">
                    <ul class="nice-list">
                        <li>
                            <div class="left">Celkem</div>
                            <div class="right"><?php echo $GLOBALS['visits']; ?></div>
                            <div class="clearer">&nbsp;</div>
                        </li>
                        <li>
                            <div class="left">Dnes</div>
                            <div class="right"><?php echo $GLOBALS['visits_day']; ?></div>
                            <div class="clearer">&nbsp;</div>
                        </li>
                        <li>
                            <div class="left">Online</div>
                            <div class="right"><?php echo $GLOBALS['visits_online']; ?></div>
                            <div class="clearer">&nbsp;</div>
                        </li>
                    </ul>
                </div>
            </div>

            <div style="text-align: center">
                <a href="http://www.facebook.com/fyzikalniolympiada" title="Fyzikální olympiáda na Facebooku"><img src="/pic/ico-facebook.png" alt="Fyzikální olympiáda na Facebooku"/></a>
            </div>
        </div>
        <div class="clearer">&nbsp;</div>
    </div>

    <div id="footer">
        <div class="left" id="footer-left">
            <img src="images/logo-small.gif" alt="" class="left" />
            <p>&copy; 2002&ndash;<?php echo date('Y') ?> Fyzikální olympiáda. All rights Reserved.<br />
                Pokud není uvedeno jinak, podléhá text na těchto stránkách licenci <a rel="license" href="http://creativecommons.org/licenses/by/3.0/cz/">Creative Commons Uveďte autora 3.0 Česká republika</a>
                <!--a rel="license" href="http://creativecommons.org/licenses/by/3.0/cz/"><img alt="Licence Creative Commons" style="border-width:0" src="http://i.creativecommons.org/l/by/3.0/80x15.png" /></a-->
            </p>
            <p><a href="mailto:webmaster@fyzikalniolympiada.cz" title="Kontaktní email">webmaster@fyzikalniolympiada.cz</a> &ndash; Jan Prachař</p>
            <p class="quiet"><a href="http://templates.arcsin.se/">Website template</a> by <a href="http://arcsin.se/">Arcsin</a></p>
            <div class="clearer">&nbsp;</div>
        </div>

        <div class="right" id="footer-right">
            <p class="large">
                <a href="rss.php?who=<?php echo $GLOBALS['who'] ?>" title="RSS kanál"><abbr title="Realy Simple Syndication - Odebírat novinky ve formátu RSS">RSS</abbr></a> <span class="text-separator">|</span>
                <a href="<?php echo odkaz('domaci.html') ?>">Domácí kolo</a> <span class="text-separator">|</span>
                <a href="<?php echo odkaz('ef.html') ?>">Kategorie E, F</a> <span class="text-separator">|</span>
                <a href="<?php echo odkaz('g.html') ?>">Archimediáda</a> <span class="text-separator">|</span>
                <a href="<?php echo odkaz('archiv.html') ?>">Archiv</a>
            </p>
        </div>
        <div class="clearer">&nbsp;</div>
        <p class="quiet small">
        </p>
    </div>
</div>
</body>
</html>
<?php
$mysql->close();
$mysql_odkazy->close();

ob_end_flush();