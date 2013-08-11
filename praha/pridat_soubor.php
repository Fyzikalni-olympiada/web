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
	."Content-type: text/plain; charset=\"utf-8\"\r\n"
	."Content-transfer-encoding: 8bit";
$options = '';

/** Nacteni z DB */
if (isset($_GET['id']) && !isset($_POST['ok'])) {
	$GLOBALS['mysql_odkazy']->query("
		SELECT filename, " . TABLE_FILES . ".title, difference, " . TABLE_MENU_STRUCTURE . ".id, parent_id, poradi, name, " . TABLE_MENU_STRUCTURE . ".title
		FROM " . TABLE_FILES . " LEFT JOIN " . TABLE_MENU_STRUCTURE . "
		ON " . TABLE_FILES . ".id=" . TABLE_MENU_STRUCTURE . ".file_id
		WHERE " . TABLE_FILES . ".id=" . $GLOBALS['mysql_odkazy']->escape_string($_GET["id"]) . "
		AND " . TABLE_MENU_STRUCTURE . ".path='" . FILE_INDEX . "'
	");
	if (list($filename, $filetitle, $difference, $menu_structure_id, $parent_id, $poradi, $name, $menutitle) = $GLOBALS['mysql_odkazy']->fetch_array()) {
		$_POST['filename'] = $filename;
		$_POST['filetitle'] = $filetitle;
		$_POST['name'] = $name;
		$_POST['menutitle'] = $menutitle;
		if ($difference) {
			$_POST['difference'] = 1;
		}
		//najdeme za_id
		$GLOBALS['mysql_odkazy']->query('
			SELECT id
			FROM ' . TABLE_MENU_STRUCTURE . '
			WHERE parent_id=' . $parent_id .'
			AND poradi<' . $poradi . '
			ORDER BY poradi DESC
		');
		if (list($za_id) = $GLOBALS['mysql_odkazy']->fetch_array()) {
			$_POST['za_id'] = $za_id;
		} else {
			$_POST['za_id'] = 0;
		}
	}
	$get_url = '?id=' . $_GET['id'];
} else {
	$get_url = '';
}

/** update odeslán */
if (isset($_POST["ok"])) { 
	$_POST['filename'] = $GLOBALS['mysql']->odstran_problemy($_POST["filename"]);
	$_POST['filetitle'] = $GLOBALS['mysql']->odstran_problemy($_POST["filetitle"]);
	$_POST['za_id'] = $GLOBALS['mysql']->odstran_problemy($_POST["za_id"]);
	$_POST['name'] = $GLOBALS['mysql']->odstran_problemy($_POST["name"]);
	$_POST['menutitle'] = $GLOBALS['mysql']->odstran_problemy($_POST["menutitle"]);
	
	if (md5($_POST["heslo"]) != $qw)  //kontrola hesla
		$chyba .= "Špatné heslo!<br />";
	
	if ($_POST["filename"] == '') {
		$chyba .= "Chybí název souboru!<br />";
	}
	if ($_POST["filetitle"] == '') {
		$chyba .= "Chybí titulek souboru!<br />";
	}
	if (array_key_exists('difference', $_POST)) {
		$difference = 1;	
	} else {
		$difference = 0;
	}
	if ($_POST['za_id'] && empty($_POST["name"])) {
		$chyba .= "Chybí název položky v menu!<br />";
	}
	if ($_POST['za_id'] && empty($_POST["menutitle"])) {
		$chyba .= "Chybí popisek k položce v menu!<br />";
	}

	if ($chyba == "") {
		$_POST['filename'] = $GLOBALS['mysql']->escape_string($_POST["filename"]);
		$_POST['filetitle'] = $GLOBALS['mysql']->escape_string($_POST["filetitle"]);
		$_POST['name'] = $GLOBALS['mysql']->escape_string($_POST["name"]);
		$_POST['menutitle'] = $GLOBALS['mysql']->escape_string($_POST["menutitle"]);

		if (isset($_GET['id']) && !array_key_exists('as_new', $_POST)) {
			$query = 'UPDATE ' . TABLE_FILES . ' SET ';
		} else {
			$query = 'INSERT INTO ' . TABLE_FILES . ' SET ';
		}
		$query .= 'filename=' . $_POST["filename"] . ', ';
		$query .= 'title=' . $_POST["filetitle"] . ', ';
		$query .= 'difference="' . $difference . '" ';
		
		if (isset($_GET['id']) && !array_key_exists('as_new', $_POST)) {
			$query .= 'WHERE id="' . mysql_escape_string($_GET['id']) . '"';
		}
		if($result = $GLOBALS['mysql']->query($query) && $_POST['za_id']) {//vložení do DB proběhlo v pořádku a chceme vytvořit položku menu
			$file_id = mysql_insert_id($GLOBALS['mysql']->dbc);
			//určíme pořadí a parent_id
			$GLOBALS['mysql']->query('
				SELECT parent_id, poradi
				FROM ' . TABLE_MENU_STRUCTURE . '
				WHERE id=' . $GLOBALS['mysql']->escape_string($_POST['za_id']) . '
			');
			if (list($parent_id, $poradi) = $GLOBALS['mysql']->fetch_array()) {
				$GLOBALS['mysql']->query('
					SELECT id, poradi
					FROM ' . TABLE_MENU_STRUCTURE . '
					WHERE parent_id=' . $GLOBALS['mysql']->escape_string($parent_id) . '
					AND poradi>"' . $poradi . '"
					ORDER BY poradi ASC
				');	
				$i = 0;
				$posunout = array();
				//zapamatujeme si, u kterých řádků si musíme posunout pořadí o 1
				while(list($id, $poradi_i) = $GLOBALS['mysql']->fetch_array()) {
					$i++;
					if ($poradi_i <= $poradi+$i) {
						$posunout = array_merge($posunout, array($id));
					}
				}
				for ($i = sizeof($posunout)-1; $i >=0; $i--) {
					$GLOBALS['mysql']->query('
						UPDATE ' . TABLE_MENU_STRUCTURE . '	SET
						poradi="' . ($poradi+$i+2) . '"
						WHERE id="' . $posunout[$i] . '"
					');
				}
				if (isset($_GET['id']) && !array_key_exists('as_new', $_POST)) {
					$query = 'UPDATE ' . TABLE_MENU_STRUCTURE . ' SET ';
				} else {
					$query = 'INSERT INTO ' . TABLE_MENU_STRUCTURE . ' SET ';
					$query .= 'file_id="' . $file_id . '", ';
					$query .= 'path="' . FILE_INDEX . '", ';
				}
				$query .= 'parent_id=' . $GLOBALS['mysql']->escape_string($parent_id) . ', ';
				$query .= 'poradi="' . ($poradi+1) . '", ';
				$query .= 'name=' . $_POST["name"] . ', ';
				$query .= 'title=' . $_POST["menutitle"] . ' ';
				if (isset($_GET['id']) && !array_key_exists('as_new', $_POST)) {
					$query .= 'WHERE file_id="' . mysql_escape_string($_GET['id']) . '" ';
					$query .= 'AND path="' . FILE_INDEX . '"';
				}
				$GLOBALS['mysql']->query($query);
			}

			header('Location: http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']));
		}
	} else {
		echo "<div class=\"AdminError\" >Update se nezdařil!<br /><br />$chyba<br />Opravte chyby a odešlete formulář znovu.</div>";
	}
}

if (!isset($_POST["ok"]) || $chyba) {
?>
<html>
<head>
<TITLE>Přidat soubor</TITLE>
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
<script language="JavaScript" type="text/javascript">
function checkit()
{
  if ((document.sign.filename.value == '') || (document.sign.title.value == '')) {
    alert('Některá pole nejsou vyplněná.');
	return false;
  }
  else {
    document.sign.submit();
  }
}
</script>

</HEAD>
<BODY bgcolor="#ffffff" fontcolor="#000000">
<h1>Přidat soubor</h1>

<?php
if (!isset($za_id)) {
	$za_id = 0;
}

if ($za_id == 0) {
	$selected = ' SELECTED';
} else {
	$selected = '';
}
	
$options = '<option value="0"' . $selected . '>--- neumísťovat do menu ---</option>\r\n';
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

while ( $row = mysql_fetch_assoc($result) ) { //hlavní menu
	if ($za_id == $row['id']) {
		$selected = ' SELECTED';
	} else {
		$selected = '';
	}
	$options .= "<option value=\"$row[id]\"" . $selected . "> $row[name] </option>\r\n";
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
		if ($za_id == $row2['id']) {
			$selected = ' SELECTED';
		} else {
			$selected = '';
		}
		$options .= "<option value=\"$row2[id]\"" . $selected . ">|-- $row2[name] </option>\r\n";
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
			if ($za_id == $row3['id']) {
				$selected = ' SELECTED';
			} else {
				$selected = '';
			}
			$options .= "<option value=\"$row3[id]\"" . $selected . ">&nbsp;&nbsp;&nbsp;|-- $row3[name] </option>\r\n";
		}
	}
}
?>


<div id="form_pridat">
<form name="sign" id="sign" action="<? echo $_SERVER['PHP_SELF'] . $get_url ?>" method="post" ENCTYPE="multipart/form-data">

<h2>Soubor</h2>

<table>
<tr>
	<td style="width: 20em; text-align:right">Cesta k souboru:</td>
	<td style="width: 30em"><input tabindex="1" type="text" class="text" size="60" value='<?php echo isset($_POST["filename"]) ? $_POST["filename"] : '' ?>' name="filename"></td>
</tr>
<tr>
	<td style="width: 20em; text-align:right">Titulek souboru:</td>
	<td><input tabindex="2" type="text" class="text" size="60" value='<?php echo isset($_POST["filetitle"]) ? $_POST["filetitle"] : '' ?>' name="filetitle"></td>
</tr>
<tr>
	<td style="width: 20em; text-align:right">Rozdílné verze souboru<br />pro student/učitel/organizátor:</td>
	<td><input tabindex="3" type="checkbox" name="difference"<?php echo isset($_POST["difference"]) ? ' checked' : '' ?>></td>
</tr>
</table>

<h2>Položka v menu</h2>

<table>
<tr>
	<td style="width: 20em; text-align:right">Zařadit za:</td>
	<td style="width: 30em"><select tabindex="4" name="za_id"><?php
	echo $options;
      ?>
      </select></td>
</tr>
<tr>
	<td style="width: 20em; text-align:right">Název položky:</td>
	<td><input tabindex="5" type="text" class="text" size="60" value='<?php echo isset($_POST["name"]) ? $_POST["name"] : '' ?>' name="name"></td>
</tr>
<tr>
	<td style="width: 20em; text-align:right">Popisek položky:</td>
	<td><input tabindex="6" type="text" class="text" size="60" value='<?php echo isset($_POST["menutitle"]) ? $_POST["menutitle"] : '' ?>' name="menutitle"></td>
</tr>
</table>

<h2>Uložit</h2>

<table>
<tr>
	<td style="width: 20em; text-align:right">Heslo:</td>
	<td style="width: 30em"><input tabindex="7" type="password" class="text" size="25" name="heslo"></td>
</tr>
<tr>
	<td style="width: 20em; text-align:right"></td>
	<td>
	<input tabindex="8" type="submit" class="submit" name="ok" value="Uložit">
	<?php if (isset($_GET['id'])) {?>
		<input type="checkbox" name="as_new">Uložit jako novou položku
	<?php }?>
	</td>
</tr>
</table>
</form>
</div>
<?php 
}
?>
</body></html>
