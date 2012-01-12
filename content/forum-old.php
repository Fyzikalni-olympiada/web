<?php
if(!defined("VALID_ACCESS"))	{die("Neoprávnìnı pøístup!");}				//	Ochrana proti neoprávnìnému pøístupu ke skriptùm

$webmaster="webmaster@fo.cuni.cz";
$headers = "From: webmaster@fo.cuni.cz\r\n"
    ."Reply-To: webmaster@fo.cuni.cz\r\n"
	."MIME-Version: 1.0\r\n"
    ."X-Mailer: PHP\r\n"
	."Content-type: text/plain; charset=\"Windows-1250\"\r\n"
	."Content-transfer-encoding: 8bit";

define("nastranku",7);

$mysql_write = new db("w",0);

if ( isset($_GET["page"]) && (intval($_GET['page']>0)))
  $strana = intval($_GET["page"]);
else
  $strana = 1;


function vypis_forum($vnoreni,$reakcena_id,$stranka)
{
	$nastranku = nastranku;
	$monthz = Array('', 'ledna', 'února', 'bøezna', 'dubna', 'kvìtna', 'èervna', 'èervence', 'srpna', 'záøí', 'øíjna', 'listopadu', 'prosince');

	$query = '
  		SELECT id, name, email, date, time, title, text
  		FROM ' . TABLE_FORUM . '
	  	WHERE vnoreni=' . $vnoreni . '
  		AND reakcena_id=' . $reakcena_id . '
		AND who LIKE \'%' . $GLOBALS['kdo'] . '%\'
	';
	if ( $vnoreni == 0 )
		$query .= '
		ORDER BY date DESC, time DESC
		LIMIT ' . (($stranka-1)*$nastranku) . ',' .  $nastranku;
	else
    	$query .= '
    	ORDER BY date, time';

	$GLOBALS['mysql']->query($query);
	$result = $GLOBALS['mysql']->vysledek;
	while ( $row = mysql_fetch_array($result) ) {
	    $s = '
<div class="center">
<table cellspacing="0" cellpadding="0">
	<tr>';
		if ( $vnoreni > 1 )
			$s .= '
		<td style="width: ' . ( 5*($vnoreni-1) ) . '%">&nbsp;</td>';
		if ( $vnoreni )
			$s .= '
		<td class="cara" style="width: 5%">&nbsp;</td>';
			
		$s .= '
		<td rowspan="2" class="text_prispevku" style="width: ' . ( 100-5*($vnoreni) ) . '%">';
		$datum = explode('-',$row["date"]);
		$s .= '
			<dl>
				<dt>
					' . $row["title"] . ' (<a href="email:' . str_replace("@",'(zavináè)',$row["email"]) . '" title="Autor pøíspìvku">' . $row["name"] . '</a>, ' . ((int) $datum[2]) . '. ' . $monthz[(int) $datum[1]] . " " . $datum[0] . ')
				</dt>
				<dd>
				' . str_replace('<BR>', '<br />', $row["text"]) . '
				</dd>
				<dd class="reagovat">
				<a href="' . odkaz2(FILE_FORUM, array('page'=>$stranka, 'vnoreni'=>$vnoreni, 'id'=>$row["id"], 'titleline'=>urlencode($row["title"]) )) . '" title="Ragovat na pøíspìvek">reagovat</a>
				</dd>
			</dl>
		</td>
	</tr>';
		
		if ( $vnoreni )
			$s .= '
	<tr>
		<td>&nbsp;</td>';
		if ( $vnoreni > 1 )
			$s .= '
		<td>&nbsp;</td>';
		if ( $vnoreni )
			$s .= '
	</tr>';

	$s .= '
</table>
</div>';
	
	echo $s;

	vypis_forum($vnoreni+1,$row["id"],$stranka);
	}
}

/* Function spam returns 0 if $text is not a recognizable spam, else it returns error message:
	1 - no name,
	2 - not a valid e-amil,
	3 - it is too short,
	4 - it is too long,
	5 - dirty word is used in it
	6 - a word in $text is longer than 20 letters
	7 - there is 5 same characters in a row
	8 - there is at least 4 same words, which repeat after same period
	9 - tag in text
*/
function spam($text, $name, $titleline, $mail)
{
  $text = $text . " " .  $name . " " . $titleline;
  $t_spam = $text . " " .  $name;
  $text = strtolower($text);
  $text = str_replace("ì", "e", $text);
  $text = str_replace("š", "s", $text);
  $text = str_replace("è", "c", $text);
  $text = str_replace("ø", "r", $text);
  $text = str_replace("", "z", $text);
  $text = str_replace("ı", "y", $text);
  $text = str_replace("á", "a", $text);
  $text = str_replace("í", "i", $text);
  $text = str_replace("é", "e", $text);
  $text = str_replace("ú", "u", $text);
  $text = str_replace("ù", "u", $text);
  $text = str_replace("", "t", $text);
  $text = str_replace("ò", "n", $text);
  $text = str_replace("ï", "d", $text);
  if ($name == '') //1
    return "Uveïte prosím Vaše jméno!";
  if(!empty($mail) && !EReg("^[[:graph:]]+@[[:graph:]]+(\.[[:graph:]]{2,})$", $mail)) //2
    return "Váš e-mail má neplatnı formát!";
  if (strlen($text) < 4) //3
    return "Váš text je pøíliš krátkı!";
  if (strlen($text) > 8000) //4
    return "Váš text je pøíliš dlouhı!";
  if (stristr($text, "kurv") || strstr($text, "prdel") || strstr($text, "pica") || strstr($text, "pice") || strstr($text, "pici") || strstr($text, "hajzl") || strstr($text, "debil") || strstr($text, "kokot") || strstr($text, "curak") || strstr($text, "kreten") || strstr($text, "srack") || strstr($text, "srat") || strstr($text, "serte") || strstr($text, "serou") || strstr($text, "srany") || strstr($text, "srani") || strstr($text, "srane") || strstr($text, " kund") || strstr($text, "curac") || strstr($text, "jebat") || strstr($text, "jeban") || strstr($text, "kurev") || strstr($text, "sukat") || strstr($text, " hovn") || strstr($text, "mrd") || strstr($text, "pazdrat") || strstr($text, "sragor") || strstr($text, "prdol") || strstr($text, "chuj") || strstr($text, "klatic") || strstr($text, "honimir") || strstr($text, "hulibrk") || strstr($text, "chcat") || strstr($text, "chcan") || strstr($text, "sulin")) //5
    return "Nepouívejte prosím v textu sprostá slova!";
  $words = split('[ 
]', $text);
  for ($i = 0; $i < count($words); $i++) //6
    if (strlen($words[$i]) > 60)
	  return "Nemùete mít slovo delší ne šedesát písmen!";
  /*for ($i = 4; $i < strlen($text); $i++) //7
    if ($text[$i-4] == $text[$i-3] && $text[$i-3] == $text[$i-2] && $text[$i-2] == $text[$i-1] && $text[$i-1] == $text[$i] && $text[$i] != " ")
      return "Ve slovì se Vám opakuje 5 stejnıch písmen za sebou!";*/
  /*for ($i = 0; $i <= count($words)-3; $i++) { //8
  if ($words[$i] != '' && $words[$i] != "Re:") {
    $text1 = strstr($text, $words[$i]);
	$text1[0] = ' ';
	$text2 = strstr($text1, $words[$i]);
	$text2[0] = ' ';
	$text3 = strstr($text2, $words[$i]);
	$text3[0] = ' ';
    $text4 = strstr($text3, $words[$i]);
	$text4[0] = ' ';
	$dif1 = strlen($text2) - strlen($text1);
	$dif2 = strlen($text3) - strlen($text2);
	$dif3 = strlen($text4) - strlen($text3);
	if ($dif1 == $dif2 && $dif2 == $dif3)
	  return "Nepokoušejte se posílat spamy!";
  }
  }*/
  /*if(EReg("<[^>]+>", $text)) //9
    return "Nepouívejte html tagy!";*/
  return 0;
};


$chyba = "";
?>
<?php if ($kdo == "student") { ?>
	<p>Toto diskusní fórum je urèeno pro studenty. Mùete se zde vyjadøovat ohlednì prùbìhu olympiády, kvality úloh, pøípadnì mít nìjaké jiné pøipomínky èi dotazy.
<?php } if ($kdo == "ucitel") { ?>
	<p>Toto diskusní fórum je urèeno pro uèitele. Mùete se zde vyjadøovat ohlednì prùbìhu olympiády, kvality úloh, pøípadnì mít nìjaké jiné pøipomínky èi dotazy.
<?php } if ($kdo == "organizator") { ?>
	<p>Toto diskusní fórum je urèeno pro organizátory. Mùete se zde vyjadøovat ohlednì prùbìhu olympiády, kvality úloh, pøípadnì mít nìjaké jiné pøipomínky èi dotazy.
<?php } ?>
	</p>
<div id="forum">
<?php
if (isset($_POST["ok"])) {     //update odeslán
  if ($_POST["titleline"] != "") {
    $date=date("Y-m-d");
    $time=date("H:i:s");	
	$_POST["titleline"] = ucfirst($_POST["titleline"]);
	//$_POST["name"] = ucfirst($_POST["name"]);
  }
  else
    $chyba .= "Chybí nadpis novinky!<br />";
  if (!($err = spam($_POST["text"],$_POST["name"],$_POST["titleline"],$_POST["email"]))) {
	$zaloha = $_POST["text"];
	$_POST["text"] = ucfirst($_POST["text"]);
	$_POST["text"] = strip_tags($_POST["text"]);
  	$_POST["text"] = str_replace("\n", "<br />", $_POST["text"]);
	$_POST["text"] = str_replace(' - ', "&nbsp;&ndash; ", $_POST["text"]);
	$_POST["text"] = str_replace(' k ', " k&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' s ', " s&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' v ', " v&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' u ', " u&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' o ', " o&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' a ', " a&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' i ', " i&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' K ', " K&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' S ', " S&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' V ', " V&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' U ', " U&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' O ', " O&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' A ', " A&nbsp;", $_POST["text"]);
	$_POST["text"] = str_replace(' I ', " I&nbsp;", $_POST["text"]);
  }
  else
    $chyba .= $err . "<br />";
  if ($_POST['kontrola'] != '2') {
    $chyba .= '1 + 1 se nerovna '.$_POST['kontrola'].'.<br />';
  }
	if ($chyba == "") {
/*----- Ochrana pøed duplicitou záznamù -----*/
	$mysql->query("
		SELECT name, email, text
		FROM forum	
		ORDER BY id DESC
		LIMIT 1
	");
	$row = $mysql->fetch_array(); 

	if ( !( $row["name"]==$_POST["name"] && $row["email"]==$_POST["email"] && $row["text"]==$_POST["text"] ) ) {
		$mysql_write->query("
			INSERT INTO `" . TABLE_FORUM . "`
				( `name` , `email` , `date` , `time` , `title` , `text` , `vnoreni` , `reakcena_id` , `who` )
			VALUES (
				'" . $_POST["name"] . "', '" . $_POST["email"] . "', '" . $date . "', '" . $time . "', '" . $_POST["titleline"] . "', '" . $_POST["text"] . "', '" . $_POST["vnoreni"] . "', '" . $_POST["id"] . "', '" . $kdo . "'
			)
		");
	
		$telo = "Na fo.cuni.cz je novy prispevek v diskusi od " . $_POST["name"] . " (" . $_POST["email"] . "):\n\n" . $_POST["titleline"] . "\n\n" . $zaloha;
		mail($webmaster, "Novy prispevek do diskuse na ".SERVER_NAME, $telo, $headers);
   
		if ( $_POST["vnoreni"] ) {
			$mysql->query("
				SELECT name, email, title, date
				FROM " . TABLE_FORUM . "
				WHERE id=" . $_POST["id"]
			);
			$row = $mysql->fetch_array();
			$datum = explode('-',$row["date"]);
			$telo = "Dobry den " . $row["name"] . ",\n\nV diskusnim foru na http://fo.cuni.cz byla pridana nova reakce na Vas pripevek \"" . $row["title"] . "\" z " . ((int) $datum[2]) . ". " . ((int) $datum[1]) . ". " . $datum[0] . ".\n\n--\nDekuji, Jan Prachar";
			mail($row["email"], "Reakce na vas prispevek ve foru fo.cuni.cz", $telo, $headers);
		} 
	}

	echo '
<div class="center">Pøidání názoru probìhlo v poøádku.</div>';
	} //if
	else
	echo '
<div class="chyba_vstupu">
	<p>
	Pøidání se nezdaøilo!<br /><br />
	' . $chyba . '<br />
	Opravte chyby a odešlete formuláø znovu.
	</p>
</div>';
}

if (!isset($_POST["ok"]) || $chyba !== '')
  $vyplnuj = 1;
else
  $vyplnuj = 0;

if (1) {
?>
<div class="center">
<table class="centered" cellspacing="0" cellpadding="0">
<tr valign="top">
  <td><br />
<form id="sign" action="<? echo odkaz(FILE_FORUM) . "?page=" . $strana; ?>" method="post" enctype="multipart/form-data">
<p>
<input type="hidden" name="vnoreni" value="<?php
if (isset($_GET["vnoreni"]))
  $vnoreni = $_GET["vnoreni"] + 1;
elseif (isset($_POST["vnoreni"]) && $vyplnuj)
  $vnoreni = $_POST["vnoreni"];
else
  $vnoreni = 0;
echo $vnoreni;
?>" />
<input type="hidden" name="id" value="<?php
if (isset($_GET["id"]))
  $id = $_GET["id"];
elseif (isset($_POST["id"]) && $vyplnuj)
  $id = $_POST["id"];
else
  $id = 0;
echo $id;
?>" />
</p>
  <table style="border-collapse: separate; border : 0px none; background-color : transparent;">
  <tr>
    <td style="text-align: right">Nadpis:</td>
    <td><input class="text" tabindex="1" type="text" size="40" value="<?php
if (isset($_GET["titleline"]))
  echo "Re: " . $_GET["titleline"];
elseif (isset($_POST["titleline"]) && $vyplnuj)
  echo $_POST["titleline"];
?>" name="titleline" /></td>
  </tr>
  <tr>
    <td style="text-align: right"><label for="text" accesskey="t"><span style="text-decoration: underline">T</span>ext:</label></td>
    <td><textarea name="text" cols="40" rows="10" id="text"><?php
if (isset($_POST["text"]) && $vyplnuj)
  echo $_POST["text"];
      ?></textarea></td>
  </tr>
  <tr>
    <td style="text-align: right">1 a 1 je:</td>
	<td><input class="text" tabindex="8" type="text" size="25" name="kontrola" value="<?php
if (isset($_POST["kontrola"]) && $vyplnuj)
  echo $_POST["kontrola"] ?>" /></td>
  </tr>
  <tr>
    <td style="text-align: right">Jméno:</td>
	<td><input class="text" tabindex="9" type="text" size="25" name="name" value="<?php
if (isset($_POST["name"]) && $vyplnuj)
  echo $_POST["name"] ?>" /></td>
  </tr>
  <tr>
    <td style="text-align: right">E-mail:</td>
	<td><input class="text" tabindex="10" type="text" size="25" name="email" value="<?php
if (isset($_POST["email"]) && $vyplnuj)
  echo $_POST["email"] ?>" /></td>
  </tr>
  <tr>
    <td></td>
	<td><input tabindex="13" type="submit" name="ok" value="Odeslat" class="button" />
    </td>
  </tr>
  </table>
  </form>
  </td>
</tr>
</table>
</div>
<div id="prispevky">
<?php
$mysql->query("
	SELECT COUNT(id) AS pocet
	FROM " . TABLE_FORUM . "
	WHERE vnoreni=0
	AND who
	LIKE '%" . $kdo . "%'"
);
$row = $mysql->fetch_array();
$pocet_stranek = ceil($row["pocet"]/nastranku);

vypis_forum(0,0,$strana);
}
?>
</div>
<p style="text-align: right">
<?php
for ( $i=1; $i<=$pocet_stranek; $i++ ) {
  if ( $i == $strana )
    echo "strana " . $i;
  else
    echo "<a href=\"" . odkaz2(FILE_FORUM, array('page'=>$i)) . "\" title=\"Pøejít na stranu " . $i . "\">strana " . $i . "</a>";
  
  if ( $i < $pocet_stranek )
    echo " | ";
}
$mysql_write->close();

?>
</p>
</div> <!-- id="forum" -->