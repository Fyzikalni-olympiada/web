<?php
echo '
<h2>Přihláška do krajského kola</h2>';

exit();

$chyba = '';
$kategorie_array = array('A'=>'kategorie A','B'=>'kategorie B','C'=>'kategorie C','D'=>'kategorie D');

/* inicializace promennych */
$school_name = null;
$referent_name = null;
$referent_phone = null;
$referent_email = null;
$participant_count = null;

if (isset($_POST['participant_count']) && is_numeric($_POST['participant_count']) && intval($_POST['participant_count']) > 0) {
	$participant_count = intval($mysql->odstran_problemy($_POST['participant_count']));
} else {
	$participant_count = 1;
	//$chyba .= 'Vyplňte prosím správný počet soutěžících.<br />';
}

/* Odeslán krok 1 */
if (isset($_POST['step1'])) { //
	if (isset($_POST['school_name']) && !empty($_POST['school_name'])) {
		$school_name = $mysql->odstran_problemy($_POST['school_name']);
	} else {
		$chyba .= 'Vyplňte prosím název Vaší školy.<br />';
	}
	if (isset($_POST['referent_name']) && !empty($_POST['referent_name'])) {
		$referent_name = $mysql->odstran_problemy($_POST['referent_name']);
	} else {
		$chyba .= 'Vyplňte prosím Vaše jméno.<br />';
	}
	if (isset($_POST['referent_phone']) && !empty($_POST['referent_phone'])) {
		$referent_phone = $mysql->odstran_problemy($_POST['referent_phone']);
	}
	if (isset($_POST['referent_email']) && !empty($_POST['referent_email'])) {
		if (preg_match("/^[[:graph:]]+@[[:graph:]]+(\.[[:graph:]]{2,})$/", $_POST['referent_email'])) {
			$referent_email = $mysql->odstran_problemy($_POST['referent_email']);
		} else {
			$chyba .= 'Vyplněný email má neplatný formát.<br />';
		}
	}

	if (empty($chyba)) {
		$mysql->query('
			INSERT INTO ' . TABLE_REFERENTS . ' SET
			referent_name=' . db::escape_string($referent_name) . ',
			referent_phone=' . db::escape_string($referent_phone) . ',
			referent_email=' . db::escape_string($referent_email) . ',
			school_name=' . db::escape_string($school_name) . ',
			rocnik="' . AKTUALNI_ROCNIK . '",
			datetime="' . date('Y-m-d H:i:s') . '"
		');
		$referent_id = mysql_insert_id($mysql->dbc);
	}
}

/* inicializace promennych */
$participant_name = array_fill(1,$participant_count,null);
$participant_surname = array_fill(1,$participant_count,null);
$participant_class = array_fill(1,$participant_count,null);
$participant_teacher = array_fill(1,$participant_count,null);
$participant_email = array_fill(1,$participant_count,null);
$participant_category = array_fill(1,$participant_count,null);
$participant_born_year = array_fill(1,$participant_count,null);
$participant_solution_count = array_fill(1,$participant_count,null);

/* Snížit nebo zvýšit počet soutěžících 2 */
if (isset($_POST['step2-increase']) || isset($_POST['step2-decrease'])) { //
	if (isset($_POST['participant_name']) && is_array($_POST['participant_name'])) {
		$participant_name = $mysql->odstran_problemy($_POST['participant_name']);
	}
	if (isset($_POST['participant_surname']) && is_array($_POST['participant_surname'])) {
		$participant_surname = $mysql->odstran_problemy($_POST['participant_surname']);
	}
	if (isset($_POST['participant_class']) && is_array($_POST['participant_class'])) {
		$participant_class = $mysql->odstran_problemy($_POST['participant_class']);
	}
	if (isset($_POST['participant_teacher']) && is_array($_POST['participant_teacher'])) {
		$participant_teacher = $mysql->odstran_problemy($_POST['participant_teacher']);
	}
	if (isset($_POST['participant_email']) && is_array($_POST['participant_email'])) {
		$participant_email = $mysql->odstran_problemy($_POST['participant_email']);
	}
	if (isset($_POST['participant_category']) && is_array($_POST['participant_category'])) {
		$participant_category = $mysql->odstran_problemy($_POST['participant_category']);
	}
	if (isset($_POST['participant_born_year']) && is_array($_POST['participant_born_year'])) {
		$participant_born_year = $mysql->odstran_problemy($_POST['participant_born_year']);
	}
	if (isset($_POST['participant_solution_count']) && is_array($_POST['participant_solution_count'])) {
		$participant_solution_count = $mysql->odstran_problemy($_POST['participant_solution_count']);
	}
	if (isset($_POST['referent_id'])) {
		$referent_id = $mysql->odstran_problemy($_POST['referent_id']);
		$mysql->query('SELECT id, school_name FROM ' . TABLE_REFERENTS . ' WHERE id=' . db::escape_string($referent_id) . '');
		if ($row = $mysql->fetch_array()) {
			$referent_id = $row['id'];
			$school_name = $row['school_name'];
		} else {
			$referent_id = null;
			$school_name = '';
		}
	}
	if (isset($_POST['step2-increase'])) {
		$participant_count++;
		$participant_name[] = null;
		$participant_surname[] = null;
		$participant_class[] = null;
		$participant_teacher[] = null;
		$participant_email[] = null;
		$participant_category[] = null;
		$participant_born_year[] = null;
		$participant_solution_count[] = null;
	} elseif ($participant_count > 1) {
		$participant_count--;
	}

}
/* Odeslán krok 2 */
if (isset($_POST['step2'])) { //
	if (isset($_POST['participant_name']) && is_array($_POST['participant_name'])) {
		$participant_name = $mysql->odstran_problemy($_POST['participant_name']);
		for ($i=1; $i<=$participant_count; $i++) {
			if (!isset($participant_name[$i]) || empty($participant_name[$i])) {
				$chyba .= 'Vyplňte jméno soutěžícího č.'.$i.'.<br />';
			}
		}
	} else {
		$chyba .= 'Neočekávaný vstup.<br />';
	}
	if (isset($_POST['participant_surname']) && is_array($_POST['participant_surname'])) {
		$participant_surname = $mysql->odstran_problemy($_POST['participant_surname']);
		for ($i=1; $i<=$participant_count; $i++) {
			if (!isset($participant_surname[$i]) || empty($participant_surname[$i])) {
				$chyba .= 'Vyplňte příjmení soutěžícího č.'.$i.'.<br />';
			}
		}
	} else {
		$chyba .= 'Neočekávaný vstup.<br />';
	}
	if (isset($_POST['participant_class']) && is_array($_POST['participant_class'])) {
		$participant_class = $mysql->odstran_problemy($_POST['participant_class']);
		for ($i=1; $i<=$participant_count; $i++) {
			if (!isset($participant_class[$i]) || empty($participant_class[$i])) {
				$participant_class[$i] = null;
			}
		}
	} else {
		$chyba .= 'Neočekávaný vstup.<br />';
	}
	if (isset($_POST['participant_teacher']) && is_array($_POST['participant_teacher'])) {
		$participant_teacher = $mysql->odstran_problemy($_POST['participant_teacher']);
		for ($i=1; $i<=$participant_count; $i++) {
			if (!isset($participant_teacher[$i]) || empty($participant_teacher[$i])) {
				$participant_teacher[$i] = null;
			}
		}
	} else {
		$chyba .= 'Neočekávaný vstup.<br />';
	}
	if (isset($_POST['participant_email']) && is_array($_POST['participant_email'])) {
		$participant_email = $mysql->odstran_problemy($_POST['participant_email']);
		for ($i=1; $i<=$participant_count; $i++) {
			if (!isset($participant_email[$i]) || empty($participant_email[$i])) {
				$participant_email[$i] = null;
			}
		}
	} else {
		$chyba .= 'Neočekávaný vstup.<br />';
	}
	if (isset($_POST['participant_category']) && is_array($_POST['participant_category'])) {
		$participant_category = $mysql->odstran_problemy($_POST['participant_category']);
		for ($i=1; $i<=$participant_count; $i++) {
			if (!isset($participant_category[$i]) || !array_key_exists($participant_category[$i], $kategorie_array)) {
				$chyba .= 'Chybně vyplněná kategorie u soutěžícího č.'.$i.'.<br />';
			}
		}
	} else {
		$chyba .= 'Neočekávaný vstup.<br />';
	}
	if (isset($_POST['participant_born_year']) && is_array($_POST['participant_born_year'])) {
		$participant_born_year = $mysql->odstran_problemy($_POST['participant_born_year']);
		for ($i=1; $i<=$participant_count; $i++) {
			if (!isset($participant_born_year[$i]) || $participant_born_year[$i] < 1990) {
				$chyba .= 'Chybně vyplněný rok narození u soutěžícího č.'.$i.'.<br />';
			}
		}
	} else {
		$chyba .= 'Neočekávaný vstup.<br />';
	}
	if (isset($_POST['participant_solution_count']) && is_array($_POST['participant_solution_count'])) {
		$participant_solution_count = $mysql->odstran_problemy($_POST['participant_solution_count']);
		for ($i=1; $i<=$participant_count; $i++) {
			if (!isset($participant_solution_count[$i]) || !is_numeric($participant_solution_count[$i])) {
				$participant_solution_count[$i] = intval($participant_solution_count[$i]);
				$chyba .= 'Vyplňte správný počet odevzdaných úloh soutěžícího č.'.$i.'.<br />';
			}
		}
	} else {
		$chyba .= 'Neočekávaný vstup.<br />';
	}
	if (isset($_POST['referent_id'])) {
		$referent_id = $mysql->odstran_problemy($_POST['referent_id']);
		$mysql->query('SELECT id, school_name FROM ' . TABLE_REFERENTS . ' WHERE id=' . db::escape_string($referent_id) . '');
		if ($row = $mysql->fetch_array()) {
			$school_name = $row['school_name'];
			$referent_id = $row['id'];
		} else {
			$chyba .= 'Omlouváme se, došlo k chybě databáze. Opakujte prosím postup znovu.<br />';
			$school_name = '';
			$referent_id = null;
		}
	} else {
		$chyba .= 'Neočekávaný vstup.<br />';
	}

	if (empty($chyba)) {
		for ($i=1; $i<=$participant_count; $i++) {
			$mysql->query('
				INSERT INTO ' . TABLE_PARTICIPANTS . ' SET
				referent_id=' . db::escape_string($referent_id) . ',
				name=' . db::escape_string($participant_name[$i]) . ',
				surname=' . db::escape_string($participant_surname[$i]) . ',
				class=' . db::escape_string($participant_class[$i]) . ',
				teacher=' . db::escape_string($participant_teacher[$i]) . ',
				email=' . db::escape_string($participant_email[$i]) . ',
				category=' . db::escape_string($participant_category[$i]) . ',
				born_year=' . db::escape_string($participant_born_year[$i]) . ',
				solution_count=' . db::escape_string($participant_solution_count[$i]) . '
			');
		}
		/*header('Location: '.odkaz('praha/content/prihlaska.php',null,0).'&step3=1&referent_id='.$referent_id);
		exit();*/
	}

}

if (!empty($chyba)) {
	echo '
<div class="chyba_vstupu">
	<p>
	' . $chyba . '
	</p>
</div>';
}

/* KROK 1*/
if ((!isset($_POST['step1']) && !isset($_POST['step2']) && !isset($_POST['step2-decrease']) && !isset($_POST['step2-increase']) && !isset($_GET['step3']) && empty($chyba)) || (isset($_POST['step1']) && !empty($chyba))) {

echo '
<p>
Vyplňte prosím následující formulář. Žádáme Vás především o&nbsp;kontakt na Vás a&nbsp;na soutěžící
za účelem rychlého doručení pozvánky do druhého kola.
</p>
<h3>Krok 1</h3>
<form action="' . odkaz('praha/content/prihlaska.php') . '" method="post">
<div>
<label for="school_name">Název školy:</label> <input type="text" id="school_name" name="school_name" size="50" value="' . $school_name . '">
</div>
<div>
<label for="referent_name">Vaše jméno:</label> <input type="text" id="referent_name" name="referent_name" size="20" value="' . $referent_name . '">
</div>
<div>
<label for="referent_phone">Váš telefon:</label> <input type="text" id="referent_phone" name="referent_phone" size="10" value="' . $referent_phone . '">
<label for="referent_email">Váš email:</label> <input type="text" id="referent_email" name="referent_email" size="20" value="' . $referent_email . '">
</div>
<div>
<label for="participant_count">Počet soutěžících:</label> <input type="text" id="participant_count" name="participant_count" size="2" value="' . $participant_count . '">
</div>
<input type="submit" value="Pokračovat" name="step1">
</form>
';
}

/* KROK 2 */

if ((isset($_POST['step1']) && !isset($_POST['step2']) && empty($chyba)) || isset($_POST['step2-increase']) || isset($_POST['step2-decrease']) || (isset($_POST['step2']) && !empty($chyba))) {

echo '
<p>
Vyplňte prosím následující formulář. Žádáme Vás především o&nbsp;kontakt na Vás a&nbsp;na soutěžící
za účelem rychlého doručení pozvánky do druhého kola.
</p>
<h3>Krok 2</h3>
<p>
Soutěžící za ' . $school_name . '.
</p>
<form action="' . odkaz('praha/content/prihlaska.php') . '" method="post">
<div class="center">
<table cellpadding="1" class="centered">
	<thead>
	<tr>
		<th>Soutěžící</th>
		<th>Jméno</th>
		<th>Příjmení</th>
		<th>Třída</th>
		<th>Učitel fyziky</th>
		<th>Email</th>
		<th>Rok narození</th>
		<th>Kategorie</th>
		<th>Počet odevzdaných úloh</th>
	</tr>
	</thead>
	<tbody>';

for ($i=1; $i<=$participant_count; $i++) {
	echo '
	<tr>
	<td style="text-align: right">'.$i.'.</td>
	<td><input type="text" id="participant_name['.$i.']" name="participant_name['.$i.']" size="10" value="' . $participant_name[$i] . '"></td>
	<td><input type="text" id="participant_surname['.$i.']" name="participant_surname['.$i.']" size="10" value="' . $participant_surname[$i] . '"></td>
	<td><input type="text" id="participant_class['.$i.']" name="participant_class['.$i.']" size="4" value="' . $participant_class[$i] . '"></td>
	<td><input type="text" id="participant_teacher['.$i.']" name="participant_teacher['.$i.']" size="15" value="' . $participant_teacher[$i] . '"></td>
	<td><input type="text" id="participant_email['.$i.']" name="participant_email['.$i.']" size="15" value="' . $participant_email[$i] . '"></td>
	<td><input type="text" id="participant_born_year['.$i.']" name="participant_born_year['.$i.']" size="4" value="' . $participant_born_year[$i] . '"></td>
	<td><select id="participant_category['.$i.']" name="participant_category['.$i.']">';
	foreach ($kategorie_array as $participant_category_through => $text) {
		if ($participant_category_through == $participant_category[$i]) {
			$selected = ' selected';
		} else {
			$selected = '';
		}
		echo '
		<option value="' . $participant_category_through . '"' . $selected . '>'.$text.'</option>';
	}
	echo '
	</select></td>
	<td style="text-align: center"><input type="text" id="participant_solution_count['.$i.']" name="participant_solution_count['.$i.']" size="2" value="' . $participant_solution_count[$i] . '"></td>
	</tr>
	';
}
echo '
</tbody>
</table>
</div>
<input type="hidden" value="' . $participant_count . '" name="participant_count">
<input type="hidden" value="' . $referent_id . '" name="referent_id">
<input type="submit" value="Snížit počet soutěžících" name="step2-decrease">
<input type="submit" value="Zvýšit počet soutěžících" name="step2-increase">
<input type="submit" value="Pokračovat" name="step2">
</form>
';
}

/* KROK 3 */

if (isset($_POST['step2']) && empty($chyba)) {
	$mysql->query('
		SELECT school_name, rocnik, referent_name, referent_phone, referent_email
		FROM ' . TABLE_REFERENTS . '
		WHERE id=' . db::escape_string($referent_id) . '
	');
	list($school_name, $rocnik, $referent_name, $referent_phone, $referent_email) = $mysql->fetch_array();
	if (empty($referent_email) && empty($referent_phone)) {
		$konakt_text = '';
	} else {
		$konakt_text = '(';
		if (!empty($referent_phone)) {
			$konakt_text .= 'telefon: '.$referent_phone;
		}
		if (!empty($referent_phone) && !empty($referent_email)) {
			$konakt_text .= ', ';
		}
		if (!empty($referent_email)) {
			$konakt_text .= 'email: '.$referent_email;
		}
		$konakt_text .= ')';
	}
	echo '
<p>
Následující seznam můžete vytisknout a přiložit k&nbsp;odevzdávaným úlohám.
</p>
<h3>Přihláška do krajského kola ' . $rocnik .'. ročníku FO pro ' . $school_name . '</h3>
<p>
Přihlášku posílá ' . $referent_name . ' ' . $konakt_text . '.
</p>
<table cellpadding="2">
	<thead>
	<tr>
		<th>Jméno</th>
		<th>Třída</th>
		<th>Učitel fyziky</th>
		<th>Email</th>
		<th>Kategorie</th>
		<th>Počet odevzdaných úloh</th>
	</tr>
	</thead>
	<tbody>';

	$mysql->query('
		SELECT name, surname, class, teacher, email, category, solution_count
		FROM ' . TABLE_PARTICIPANTS . '
		WHERE referent_id=' . db::escape_string($referent_id) . '
	');
	while(list($name, $surname, $class, $teacher, $email, $category, $solution_count) = $mysql->fetch_array()) {

		echo '
	<tr>
	<td>' . $name . ' ' . $surname . '</td>
	<td>' . $class . '</td>
	<td>' . $teacher . '</td>
	<td>' . $email . '</td>
	<td style="text-align: center">' . $category . '</td>
	<td style="text-align: center">' . $solution_count . '</td>
	</tr>
	';
	}
echo '
</tbody>
</table>';
}


?>
