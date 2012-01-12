<?php
define("VALID_ACCESS", 1);

require_once('init.php');

$chyba = '';
$qw = '56376d48b6b39b32cbd9a97662f50cbd';
//kL7139eR;
$headers = "From: webmaster@fyzikalniolympiada.cz\r\n"
    ."Reply-To: webmaster@fyzikalniolympiada.cz\r\n"
	."MIME-Version: 1.0\r\n"
    ."X-Mailer: PHP\r\n"
	."Content-type: text/plain; charset=\"Windows-1250\"\r\n"
	."Content-transfer-encoding: 8bit";
$monthz = Array('', 'ledna', 'února', 'bøezna', 'dubna', 'kvìtna', 'èervna', 'èervence', 'srpna', 'záøí', 'øíjna', 'listopadu', 'prosince');
$options = '';

$mysql_w = new db('w', 0);

/** Nacteni z DB */
if (isset($_GET['id']) && !isset($_POST['ok'])) {
	$GLOBALS['mysql']->query('
		SELECT name, email, subject, text, who
		FROM ' . TABLE_NEWS . '
		WHERE id="' . mysql_escape_string($_GET['id']) . '"
	');
	if (list($name, $email, $subject, $text, $who) = $GLOBALS['mysql']->fetch_array()) {
		$_POST['name'] = $name;
		$_POST['email'] = $email;
		$_POST['titleline'] = $subject;
		$text = str_replace('" . odkaz("', 'ß', $text);
		$text = str_replace('") . "', 'ß', $text);
		$text = str_replace('\"', '"', $text);
	
		$_POST['text'] = $text;
		foreach (($whoArray = explode(',', $who)) as $prvek) {
			switch ($prvek) {
					case 'student':
						$_POST['stud'] = 1;
						break;
					case 'ucitel':
						$_POST['ucit'] = 1;
						break;
					case 'organizator':
						$_POST['orga'] = 1;
						break;
					default:
						break;
			}	
		}
	}
	$get_url = '?id=' . $_GET['id'];
} else {
	$get_url = '';
}

/** update odeslán */
if (isset($_POST["ok"])) { 
	$_POST["titleline"] = $GLOBALS['mysql']->odstran_problemy($_POST["titleline"]);
	$_POST["text"] = $GLOBALS['mysql']->odstran_problemy($_POST["text"]);
	$_POST["name"] = $GLOBALS['mysql']->odstran_problemy($_POST["name"]);
	$_POST["email"] = $GLOBALS['mysql']->odstran_problemy($_POST["email"]);
	$_POST["align"] = $GLOBALS['mysql']->odstran_problemy($_POST["align"]);
	$_POST["hspace"] = $GLOBALS['mysql']->odstran_problemy($_POST["hspace"]);
	$_POST["vspace"] = $GLOBALS['mysql']->odstran_problemy($_POST["vspace"]);
	$_POST["alt"] = $GLOBALS['mysql']->odstran_problemy($_POST["alt"]);


	if (md5($_POST["heslo"]) != $qw)  //kontrola hesla
		$chyba .= "Špatné heslo!<BR>";
	
	$prokohoArray = array();
	if (array_key_exists("stud", $_POST)) {
		$prokohoArray[] = 'student';
	}
	if (array_key_exists("ucit", $_POST)) {
		$prokohoArray[] = 'ucitel';
	}
	if (array_key_exists("orga", $_POST)) {
		$prokohoArray[] = 'organizator';
	}
	if (empty($prokohoArray)) {
		$chyba .= "Není urèeno pro koho novinka je.<BR>";
	} else {
		$prokoho = implode(',', $prokohoArray);
	}
	
	if ($_POST["titleline"] != "") {
		$date = date("Y-m-d");
		$time = date("H:i:s");	
		$_POST["titleline"] = ucfirst($_POST["titleline"]);
	}
	else
		$chyba .= "Chybí nadpis novinky!<BR>";
	
	if ( $_FILES['foto']['size'] !== 0 ) {       //odeslán i obrázek
		if ( $_FILES['foto']['type'] == "image/jpeg"
			|| $_FILES['foto']['type'] == "image/pjpeg" 
			|| $_FILES['foto']['type'] == "image/gif" 
			|| $_FILES['foto']['type'] == "image/x-png"
		) { //podporovaný formát
			$name = date("Ymdhis_") . $_FILES['foto']['name'];
			if (!file_exists("pic/$name") and ($_FILES['foto']['size'] <= 1050000)){
				copy($_FILES['foto']['tmp_name'], ROOT_DIR."upload/" . $name);
				$pic_id = '';
				// zjistíme pic_id (pokud obrázek existuje, zmìníme ho)
				if (isset($_GET['id'])) {
					$GLOBALS['mysql']->query('
						SELECT pic_id FROM ' . TABLE_NEWS . '
						WHERE id="' . mysql_escape_string($_GET['id']) . '"
					');
					if (($row = $GLOBALS['mysql']->fetch_array()) && !is_null($row['pic_id'])) {
						$pic_id = $row['pic_id'];
					}
				}
				$values = "'" . $pic_id . "', '" . $name . "'";
				$_POST["align"]  != "" ? $values .= ", " . $GLOBALS['mysql']->escape_string($_POST["align"]) : $values .= ", 'left'";
				$_POST["hspace"] != "" ? $values .= ", " . $GLOBALS['mysql']->escape_string($_POST["hspace"]) : $values .= ", NULL";
				$_POST["vspace"] != "" ? $values .= ", " . $GLOBALS['mysql']->escape_string($_POST["vspace"]) : $values .= ", NULL";
				$_POST["alt"]    != "" ? $values .= ", "  . $GLOBALS['mysql']->escape_string($_POST["alt"]) : $values .= ", ''";
				
				$query_img = "REPLACE INTO `" . TABLE_IMAGES . "` ( `id` , `filename` , `align` , `hspace` , `vspace` , `alt` ) VALUES ( " . $values . " ) ";
			} else {
				$chyba .= "Obrázek se nepodaøilo uploadovat, je pøíliš veliký.";
			}
		} else {
		  $chyba .= "Obrázek má nepodporovaný formát!<BR>";
		}
	}

	if ($_POST["text"] != "") {
		$zaloha = $_POST["text"];
		$_POST["text"] = ucfirst($_POST["text"]);
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
		
		//zpracovani odkazù
			$text = str_replace('"', '\"', $_POST["text"]);
		
		$pieces = explode("ß", $text);
		for ( $i=0; $i+1 < count($pieces); $i+=2 ) {
			$pieces[$i+1] = "\" . odkaz(\"" . $pieces[$i+1] . "\") . \"";
		}
		$text = implode("", $pieces);
	} else {
		$chyba .= "Chybí text pøíspìvku.<br />";
	}

	if ($chyba == "") {
		if ( isset($query_img) ) {
			$result_img = $mysql_w->query($query_img);
			$pic_id = mysql_insert_id($mysql_w->dbc);
		} else {
			$pic_id = null;
		}
		$_POST["titleline"] = $GLOBALS['mysql']->escape_string($_POST["titleline"]);
		$_POST["name"] = $GLOBALS['mysql']->escape_string($_POST["name"]);
		$_POST["email"] = $GLOBALS['mysql']->escape_string($_POST["email"]);
		$text = $GLOBALS['mysql']->escape_string($text);
	
		if (isset($_GET['id'])) {
			$query = 'UPDATE ' . TABLE_NEWS . ' SET ';
		} else {
			$query = 'INSERT INTO ' . TABLE_NEWS . ' SET ';
			$query .= 'date="' . $date . '", ';
			$query .= 'time="' . $time . '", ';
		}
		if (!is_null($pic_id)) {
			$query .= 'pic_id=' . $pic_id . ', ';
		}	
		$query .= 'name=' . $_POST["name"] . ', ';
		$query .= 'email=' . $_POST["email"] . ', ';
		$query .= 'subject=' . $_POST["titleline"] . ', ';
		$query .= 'text=' . $text . ', ';
		$query .= 'who="' . $prokoho . '", ';
		$query .= 'wheres="' . NOVINKY_WHERES . '" ';
		if (isset($_GET['id'])) {
			$query .= 'WHERE id="' . mysql_escape_string($_GET['id']) . '"';
		}
		if($result = $mysql_w->query($query)) {
			if (array_key_exists('mail', $_POST)) { //poslat e-maily
				$query = "SELECT name, email FROM `" . TABLE_USERS . "`";
				$result = $GLOBALS['mysql']->query($query);
				$subject = "Novinky na strance " . SERVER_NAME . " z " . date("d.m.Y");
				while ( $row = mysql_fetch_array($result) ) {
					$telo = "Vazeny " . $row["name"] . ",\nna " . SERVER_NAME . " je novy clanek " . $_POST["titleline"] . " od " . $_POST["name"] . ".
					
					webmaster@fyzikalniolympiada.cz";
					mail($row["email"], $subject, $telo, $headers);
				}
			}
			header('Location: http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']));
		}
	} else {
		echo "<div class=\"AdminError\" >Update se nezdaøil!<br /><br />$chyba<br />Opravte chyby a odešlete formuláø znovu.</div>";
	}
}

if (!isset($_POST["ok"]) || $chyba) {
?>
<html>
<head>
<TITLE>Pøidat novinku</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1250" />
<meta http-equiv="Content-language" content="cs" />
<!-- ENCODING end /-->

<!-- CACHE /--> 
<!-- CACHE - MSIE /--> 
<meta http-equiv="Cache-Control" content="must-revalidate, post-check=0, pre-check=0" /> 
<meta http-equiv="Pragma" content="public" /> 
<!-- CACHE - MSIE end /--> 
<!-- CACHE - other browsers /--> 
<meta http-equiv="Cache-Control" content="no-cache" /> 
<meta http-equiv="Pragma" content="no-cache" /> 
<meta http-equiv="Expires" content="-1" /> 
<!-- CACHE - other browsers end /--> 
<!-- CACHE - end /--> 
<meta name="robots" content="noindex,nofollow" /> 
<meta name="googlebot" content="noindex,nofollow,nosnippet,noarchive" />
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" /> 
<link rel="stylesheet" type="text/css" media="screen" href="css/admin.css" />
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
function thumb()
{
  document.thumbpic.src='';
  if (document.sign.foto.value != '') {
    document.thumbpic.src = document.sign.foto.value;
  }
  document.thumbpic.alt = document.sign.alt.value;
  document.thumbpic.align = document.sign.align.value;
  //obteci.innerHTML = document.sign.text.value;
  if (document.sign.hspace.value) {
    document.thumbpic.hspace = document.sign.hspace.value;
  }
  if (document.sign.vspace.value) {
    document.thumbpic.vspace = document.sign.vspace.value;
  }
}

function checkit()
{
  if ((document.sign.titleline.value == '') || (document.sign.text.value == '') || (document.sign.name.value == '') || (document.sign.email.value == '') || (document.sign.password.value == '')) {
    alert('Nìkterá pole nejsou vyplnìná.');
	return false;
  }
  else {
    document.sign.submit();
  }
}

function addlink()
{
	if(document.sign.linkurl.value != '') {
		addtext = '<a href="';
		addtext += document.sign.linkurl.value;
		addtext += '" title="' + document.sign.linktitle.value;
		addtext += '">' + document.sign.linktext.value;
		addtext += '</a>';
		document.sign.text.value += addtext;
	}
}

function predef()
{
<?php
$query = "
	SELECT ms.id AS id, ms.type AS type, ms.href AS href, ms.name AS name, ms.title AS title, f.filename AS filename
	FROM " . TABLE_MENU_STRUCTURE . " AS ms
	LEFT JOIN " . TABLE_FILES . " AS f
		ON (ms.file_id = f.id)
	WHERE ms.parent_id IS NULL
	AND ms.path='" . FILE_INDEX . "'
	ORDER BY ms.poradi
";
$result = $GLOBALS['mysql']->query($query);

$i = 0;
while ( $row = mysql_fetch_assoc($result) ) { //hlavní menu
	$options .= "<option value=\"link$i\"> $row[name] </option>\r\n";
	echo "
if (document.sign.linkpredef.value=='link$i'){";
	if ($row['type'] == 'file') {
		echo "
    document.sign.linkurl.value='ß$row[filename]ß';";
	} elseif ($row['type'] == 'url') {
		echo "
    document.sign.linkurl.value='$row[href]';";
	} else {
		echo "
    document.sign.linkurl.value='';";
	}
	echo "	
	document.sign.linktext.value='$row[name]';
    document.sign.linktitle.value='$row[title]';
}";
	$i++;
	$query = "
		SELECT ms.id AS id, ms.type AS type, ms.href AS href, ms.name AS name, ms.title AS title, f.filename AS filename
		FROM " . TABLE_MENU_STRUCTURE . " AS ms
		LEFT JOIN " . TABLE_FILES . " AS f
			ON (ms.file_id = f.id)
		WHERE ms.parent_id='" . $row['id'] . "'
		ORDER BY ms.poradi
	";
	$result2 = $GLOBALS['mysql']->query($query);
	while ( $row2 = mysql_fetch_array($result2) ) { //submenu
		$options .= "<option value=\"link$i\">|-- $row2[name] </option>\r\n";
		echo "
if (document.sign.linkpredef.value=='link$i'){";
		if ($row2['type'] == 'file') {
			echo "
    document.sign.linkurl.value='ß$row2[filename]ß';";
		} elseif ($row2['type'] == 'url') {
			echo "
    document.sign.linkurl.value='$row2[href]';";
		} else {
			echo "
    document.sign.linkurl.value='';";
		}
		echo "	
	document.sign.linktext.value='$row2[name]';
    document.sign.linktitle.value='$row2[title]';
}";
		$i++;
		$query = "
			SELECT ms.id AS id, ms.type AS type, ms.href AS href, ms.name AS name, ms.title AS title, f.filename AS filename
			FROM " . TABLE_MENU_STRUCTURE . " AS ms
			LEFT JOIN " . TABLE_FILES . " AS f
				ON (ms.file_id = f.id)
			WHERE ms.parent_id='" . $row2['id'] . "'
			ORDER BY ms.poradi
		";
		$result3 = $GLOBALS['mysql']->query($query);
		while ( $row3 = mysql_fetch_array($result3) ) { //subsubmenu
			$options .= "<option value=\"link$i\">&nbsp;&nbsp;&nbsp;|-- $row3[name] </option>\r\n";
			echo "
if (document.sign.linkpredef.value=='link$i'){";
			if ($row3['type'] == 'file') {
				echo "
    document.sign.linkurl.value='ß$row3[filename]ß';";
			} elseif ($row3['type'] == 'url') {
				echo "
    document.sign.linkurl.value='$row3[href]';";
			} else {
				echo "
    document.sign.linkurl.value='';";
			}
			echo "	
	document.sign.linktext.value='$row3[name]';
    document.sign.linktitle.value='$row3[title]';
}";
			$i++;
		}
	}
}
?>
}
   _editor_url = "<?php echo ROOT_WWW ?>htmlarea/";
   _editor_lang = "cz";
</SCRIPT>
<script type="text/javascript" src="<?php echo ROOT_WWW ?>htmlarea/htmlarea.js">
</script>
<script type="text/javascript" defer="1">

var config = new HTMLArea.Config(); // create a new configuration object
                                    // having all the default values
config.toolbar = [
[ //"fontname", "space",
  //"fontsize", "space",
  "formatblock", "space",
  "bold", "italic", "underline", "separator",
  "strikethrough", "subscript", "superscript", "separator",
  "copy", "cut", "paste", "space", "undo", "redo" ],
		
[ "justifyleft", "justifycenter", "justifyright", "justifyfull", "separator",
  "insertorderedlist", "insertunorderedlist", "outdent", "indent", "separator",
  /*"forecolor", "hilitecolor",*/ "textindicator", "separator",
  "inserthorizontalrule", /*"createlink", "insertimage",*/ "inserttable", "htmlmode", "separator",
  /*"popupeditor", "separator",*/ "showhelp", "about" ]
];


    HTMLArea.replace('text', config);
</script>

</HEAD>
<BODY bgcolor="#ffffff" fontcolor="#000000">
<h1>Pøidat novinku</h1>

<div id="form_pridat">
<form name="sign" id="sign" action="<? echo $_SERVER['PHP_SELF'] . $get_url ?>" method="post" ENCTYPE="multipart/form-data">
<table align=center cellspacing="0" cellpadding="0">
<tr valign="top">
  <td colspan="2"><h2>Data k updatování</h2>
  <table>
  <tr>
    <td align="right">Nadpis:</td>
    <td><input tabindex="1" type="text" class="text" size="80" value='<?php echo isset($_POST["titleline"]) ? $_POST["titleline"] : '' ?>' name="titleline"></td>
  </tr>
  <tr>
    <td align="right"><label for="text" accesskey="t"><u>T</u>ext:</label></td>
    <td><TEXTAREA tabindex="2" name="text" id="text" cols="100" rows="20"><?php echo isset($_POST["text"]) ? $_POST["text"] : '' ?></TEXTAREA></td>
  </tr>
  </table>
  </td>
</tr>
<tr valign="top">
  <td>
  <table width="90%">
  <tr>
    <td colspan=2><h3>Obrázek:</h3></td>
  </tr>
  <tr>
    <td></td>
	<td><input type="file" class="text" tabindex="17" name="foto" size="25"></td>
  </tr>
  <tr>
    <td align="right">Popisek:</td>
	<td><input type="text" class="text" size="25" name="alt" value='<?php echo isset($_POST["alt"]) ? $_POST["alt"] : '' ?>'>
	<input tabindex="15" type="button" class="button" value="Náhled" onclick="thumb()" />
	</td>
  </tr>
  <tr>
    <td align="right">Zarovnání:</td>
	<td>
	<select name="align">
      <option value= "left" <?php echo isset($_POST["align"]) && $_POST["align"] == "left" ? "selected" : ""; ?>>left</option>
      <option value="right" <?php echo isset($_POST["align"]) && $_POST["align"] == "right" ? "selected" : "";?>>right</option>
    </select>
	hspace: <input type="text" class="text" size="2" name="hspace" value='<?php echo isset($_POST["hspace"]) ? $_POST["hspace"] : '' ?>'>
    vspace: <input type="text" class="text" size="2" name="vspace" value='<?php echo isset($_POST["vspace"]) ? $_POST["vspace"] : '' ?>'>
	</td>
  </tr>
  </table>
  <table width="90%">
  <tr>
    <td align="right">Pro koho:</td>
	<td>
		<input type="checkbox" name="stud" tabindex="9" <?php echo isset($_POST["stud"]) && $_POST["stud"] ? "checked" : ""; ?>>student
		<input type="checkbox" name="ucit" tabindex="10" <?php echo isset($_POST["ucit"]) && $_POST["ucit"] ? "checked" : ""; ?>>uèitel
		<input type="checkbox" name="orga" tabindex="11" <?php echo isset($_POST["orga"]) && $_POST["orga"] ? "checked" : ""; ?>>organizátor
	</td>
  </tr>
  <tr>
    <td align="right">Jméno:</td>
	<td><input tabindex="12" type="text" class="text" size="25" name="name" value='<?php echo isset($_POST["name"]) ? $_POST["name"] : ''?>'></td>
  </tr>
  <tr>
    <td align="right">E-mail:</td>
	<td><input tabindex="13" type="text" class="text" size="25" name="email" value='<?php echo isset($_POST["email"]) ? $_POST["email"] : '' ?>'></td>
  </tr>
  <tr>
    <td align="right">Heslo:</td>
	<td><input tabindex="14" type="password" class="text" size="25" name="heslo"></td>
  </tr>
  <tr>
    <td></td>
	<td>
	<input tabindex="16" type="submit" class="submit" name="ok" value="Uložit">
	<input type="checkbox" name="mail" checked>e-mail
    </td>
  </tr>
  
  </table>
  </td>
 
  <td align="right">
  <table width="90%">
  <tr>
    <td colspan="2"><h3>Vložit odkaz na konec textu:<br /> (lze jen v textovém režimu)</h3></td>
  </tr>
  <tr valign="top">
    <td align="right">Odkaz na:</td>
	<td><select name="linkpredef" onchange="predef()" tabindex="7"><?php
	echo $options;
      ?>
      </select></td>
  </tr>
  <tr>
    <td align="right">URL:</td>
	<td><input type="text" class="text" size="30" name="linkurl" value="http://"></td>
  </tr>
  <tr>
    <td align="right">Text odkazu:</td>
	<td><input type="text" class="text" size="30" name="linktext"></td>
  </tr>
  <tr>
    <td align="right">Title:</td>
	<td><input type="text" class="text" size="30" name="linktitle"></td>
  </tr>
  <tr>
  	<td></td>
	<td>
	<input type="button" class="button" value="Vložit odkaz" tabindex="8" onclick="addlink()">
	<!--<input value="Reset" type="reset" class="button">-->
    </td>
  </tr>
  </table>
  
  <table width="90%">
  <tr>
    <td colspan="2"><h3>Náhled obrázku:</h3></td>
  </tr>
  <tr>
    <td><img name="thumbpic" src="" alt="náhled"></td>
  </tr>
  </table>
  </td>
 </tr>
</table>
</form>
</div>
<?php 
}
?>
</body></html>