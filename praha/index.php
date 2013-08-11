<?php
define("VALID_ACCESS", 1);
ob_start();

require_once('init.php');

/*
 * HTML KOD
 */

?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 
'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'> 
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='cs' lang='cs'>
<head>
<!-- ENCODING /--> 
<meta http-equiv="Content-Type" content="text/html; charset=windows-1250" />
<meta http-equiv='Content-language' content='cs' />
<!-- ENCODING end /-->

<!-- CACHE /--> 
<!-- CACHE - MSIE /--> 
<meta http-equiv='Cache-Control' content='must-revalidate, post-check=0, pre-check=0' /> 
<meta http-equiv='Pragma' content='public' /> 
<!-- CACHE - MSIE end /--> 
<!-- CACHE - other browsers /--> 
<meta http-equiv='Cache-Control' content='no-cache' /> 
<meta http-equiv='Pragma' content='no-cache' /> 
<meta http-equiv='Expires' content='-1' /> 
<!-- CACHE - other browsers end /--> 
<!-- CACHE - end /--> 

<!-- ROBOTS /--> 
<meta name='robots' content='index,follow' /> 
<meta name='googlebot' content='index,follow,snippet,archive' />
<!-- ROBOTS end /--> 

<!-- KEYWORDS & CATEGORIES - but who cares now :-( /--> 
<meta name='description' content='Fyzikální olympiáda, Praha, oficiální stránky' /> 
<meta name='keywords' content='fyzika, fyzikální, fyzikalni, olympiada, olympiáda, soutěž, soutez, 
praha' /> 
<meta name='category' content='physics' /> 
<!-- KEYWORDS & CATEGORIES - end /--> 

<!-- AUTHOR self promo - use 'crypted' e-mails defeats robotic harvesters /--> 
<meta name='author' content='All: Jan Prachař, e-mail: jan.prachar@fyzikalniolympiada.cz' /> 
<meta name='webmaster' content='All: Jan Prachař, e-mail: webmaster@fyzikalniolympiada.cz' /> 
<meta name='copyright' content='&copy;2005-2005 Jan Prachař, e-mail: jan.prachar@fyzikalniolympiada.cz' /> 
<!-- AUTHOR self promo - end /--> 

<!-- GEOURL /--> 
<meta name='ICBM' content='50.1152, 14.448' /> 
<!-- GEOURL - end /-->
          
<!-- BROWSER SPECIFIC FEATURES = ALL OFF /--> 
<!-- MSIE - 'helpful' features /--> 
<!-- <meta http-equiv='imagetoolbar' content='no' /> 
<meta http-equiv='MSThemeCompatible' content='no' /> 
<meta name='MS.LOCALE' content='cs' /> -->
<!-- OPERA - image resizing /--> 
<meta name='autosize' content='off' /> 
<!-- BROWSER SPECIFIC FEATURES = end /-->

<base href="http://<?= SERVER_NAME ?>/" />

<!-- ICON /--> 
<link rel='shortcut icon' type='image/x-icon' href='favicon.ico' /> 
<!-- ICON end /--> 

<link rel='alternate' type='application/rss+xml' title='Aktuality Fyzikální olympiády v Praze' href='rss.php'/>

<!-- NAVIGATION - based on logical relations of documents /--> 
<!-- homepage /--> 
<link rel='home' href='/' /> 
<!-- NAVIGATION - end /--> 

<!-- CASCADING STYLE SHEETS /--> 
<!-- INPAGE STYLE - pagemargin problem solving /--> 
<style type='text/css' media='all'> 
        BODY, HTML {
                border: 0px none; 
                margin: 0px;
                padding: 0px; 
        } 
</style> 
<!-- LINKED STYLE /--> 
                <link rel="stylesheet" type="text/css" media="screen" href="./css/screen.css" />
                <!--[if lte IE 6]>
                        <link rel="stylesheet" type="text/css" media="screen" 
href="./css/fixed_ie.css" />
                <![endif]-->
				<link rel='stylesheet' type='text/css' media='print' href='./css/print.css' /> 
<!-- CASCADING STYLE SHEETS - end /--> 

<title> 
Fyzikální olympiáda Praha<?php echo ' :: ' . nadpis(); ?>
</title> 
</head>

<body id="fo-cuni-cz_praha">

                <div class="hide">
                        <p>
                                This site will look much better in a browser that supports 
                                <a href="http://www.webstandards.org/upgrade/" 
                                title="Download a browser that complies with Web standards.">web 
                                standards</a>, but it is accessible to any browser or Internet 
                                device.
                        </p>
                        <hr class="hide" />
                </div>

                <div id="fixed">
                        <div id="pageFrameMask">
                                <div id="leftColumnMask"></div>
                                <div id="contentColumnMask"></div>
                        </div>
                </div>

                <div id="flowing">
                        <div id="pageFrame">
        
                                <div id="masthead">
                                        <div id="innerMasthead">
                                                <h1>Fyzikální olympiáda &ndash; Praha</h1>
                                                <hr class="hide" />
                                        </div>
                                </div>
        
                                <div class="hnav">
                                        <?php echo menu(); ?>
                                </div>
        
                                <div id="leftColumn">
                                        <div id="innerLeftColumn">
                                                <div class="vnav">

                                                        <h3><?php echo nadpis(); ?></h3>
                                                        <?php echo submenu($GLOBALS['parentID']); ?>
        
                                                </div>

                                                <!--<p>
                                                        <a href="http://validator.w3.org/check/referer"><img
                                                                src="http://www.w3.org/Icons/valid-xhtml11"
                                                                alt="Valid XHTML 1.1!" height="31" width="88" /></a>
                                                </p>
                                                <p>                                     
                                                        <a href="http://jigsaw.w3.org/css-validator/"><img
                                                                src="http://jigsaw.w3.org/css-validator/images/vcss"
                                                                alt="Valid CSS!" height="31" width="88"/></a>
                                                </p>-->
                                                <div id="counter">
                                                                        <table>
                                                                        <tr>
                                                                                <td colspan="2"><strong>Návštěvy:</strong></td>
                                                                        </tr>
                                                                        <tr>
                                                                                <td>celkem</td>
                                                                                <td style="text-align: right"><?php echo $GLOBALS['visits']; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                                <td>dnes</td>
                                                                                <td style="text-align: right"><?php echo $GLOBALS['visits_day']; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                                <td>online</td>
                                                                                <td style="text-align: right"><?php echo $GLOBALS['visits_online']; ?></td>
                                                                        </tr>
                                                                        </table>
                                                </div>
                                        </div>
                                </div>
                                <div id="contentColumn">
                                        <div id="innerContentColumn">
                                                <hr class="hide" />
                                                <a name="skipToContent" id="skipToContent"></a>
                                                <div id="content">
                                <?php
if ($napln == FILE_NEWS) {
        echo '
                                <p>Oficiální stránky pražské krajské komise souteže Fyzikální olympiáda &ndash; <a href="http://praha.fyzikalniolympiada.cz">praha.fyzikalniolympiada.cz</a></p>';
        include(ROOT_DIR.FILE_NEWS);
} else {
        include(ROOT_DIR.$napln);
}
                                ?>
                                        
                                                </div>
                                        </div>
                                </div>
                                <div id="footer">
                                        <div id="footerBorder">
                                                <div id="innerFooter">
                                                        <hr class="hide" />
                                                        &copy; 2004&ndash;<?php echo date('Y') ?> Jan Prachař &ndash; <a href="mailto:webmaster@fyzikalniolympiada.cz" title="Kontaktní email">webmaster@fyzikalniolympiada.cz</a>
                                                </div>
                                        </div>
                                </div>
                        </div>
                        <div id="bottomColorMask"></div>
                </div>
        </body>

</html>
<?php
ob_end_flush();
$mysql->close();
$mysql_odkazy->close();
?> 