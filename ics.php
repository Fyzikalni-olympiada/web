<?php
/* Termíny aktuálního ročníku jako iCalendar (/terminy.ics).
 * Rozsah události se parsuje z lidského textu termínu ("3.–6. února 2026",
 * "duben 2026", …); když se to nepovede, použije se pole date. Celodenní
 * události, DTEND je podle RFC 5545 exkluzivní (den po konci). */

include_once('init.php');

const ICS_MESICE = array(
	'ledna' => 1, 'února' => 2, 'března' => 3, 'dubna' => 4, 'května' => 5,
	'června' => 6, 'července' => 7, 'srpna' => 8, 'září' => 9, 'října' => 10,
	'listopadu' => 11, 'prosince' => 12,
	'leden' => 1, 'únor' => 2, 'březen' => 3, 'duben' => 4, 'květen' => 5,
	'červen' => 6, 'červenec' => 7, 'srpen' => 8, 'říjen' => 10,
	'listopad' => 11, 'prosinec' => 12,
);

/** [začátek, konec] jako [rok, měsíc, den] podle textu termínu, jinak null */
function ics_rozsah($termin, $rok_fallback)
{
	$t = str_replace(array("\u{a0}", '&nbsp;'), ' ', $termin);
	$t = preg_replace('~^(pondělí|úterý|středa|čtvrtek|pátek|sobota|neděle)\s+~u', '', trim($t));
	$t = preg_replace('~^do\s+~u', '', $t);
	$m = '(' . implode('|', array_keys(ICS_MESICE)) . ')';

	/* "3.–6. února 2026" */
	if (preg_match('~^(\d+)\.\s*[–-]\s*(\d+)\.\s*' . $m . '\s*(\d{4})$~u', $t, $x)) {
		$mes = ICS_MESICE[$x[3]];
		return array(array($x[4], $mes, $x[1]), array($x[4], $mes, $x[2]));
	}
	/* "6. dubna – 31. května 2026" */
	if (preg_match('~^(\d+)\.\s*' . $m . '\s*[–-]\s*(\d+)\.\s*' . $m . '\s*(\d{4})$~u', $t, $x)) {
		return array(array($x[5], ICS_MESICE[$x[2]], $x[1]), array($x[5], ICS_MESICE[$x[4]], $x[3]));
	}
	/* "5. ledna 2026" */
	if (preg_match('~^(\d+)\.\s*' . $m . '\s*(\d{4})$~u', $t, $x)) {
		$d = array($x[3], ICS_MESICE[$x[2]], $x[1]);
		return array($d, $d);
	}
	/* "1. 10. 2025" */
	if (preg_match('~^(\d+)\.\s*(\d+)\.\s*(\d{4})$~u', $t, $x)) {
		$d = array($x[3], $x[2], $x[1]);
		return array($d, $d);
	}
	/* "duben 2026" — celý měsíc */
	if (preg_match('~^' . $m . '\s*(\d{4})$~u', $t, $x)) {
		$mes = ICS_MESICE[$x[1]];
		return array(array($x[2], $mes, 1), array($x[2], $mes, cal_days_in_month(CAL_GREGORIAN, $mes, (int)$x[2])));
	}
	return null;
}

function ics_datum($d)
{
	return sprintf('%04d%02d%02d', $d[0], $d[1], $d[2]);
}

$radky = array(
	'BEGIN:VCALENDAR',
	'VERSION:2.0',
	'PRODID:-//Fyzikální olympiáda//terminy//CS',
	'CALSCALE:GREGORIAN',
	'X-WR-CALNAME:Fyzikální olympiáda – ' . AKTUALNI_ROCNIK . '. ročník',
);

foreach (data_terms(AKTUALNI_ROCNIK) as $kategorie => $terminy) {
	$kat = $kategorie === 'spolecne' ? '' : ' (kat. ' . preg_replace('~(?<=\w)(?=\w)~', ', ', $kategorie) . ')';
	foreach ($terminy as $i => $row) {
		$rozsah = ics_rozsah($row['termin'], null);
		if ($rozsah === null) {
			$d = explode('-', $row['date']);
			$rozsah = array($d, $d);
		}
		$konec = new DateTime(ics_datum($rozsah[1]));
		$konec->modify('+1 day');
		$nazev = html_entity_decode(mb_ucfirst($row['nazev']), ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$nazev = str_replace("\u{a0}", ' ', $nazev);
		$radky[] = 'BEGIN:VEVENT';
		$radky[] = 'UID:fo-' . AKTUALNI_ROCNIK . '-' . $kategorie . '-' . $i . '@fyzikalniolympiada.cz';
		$radky[] = 'DTSTAMP:' . ics_datum($rozsah[0]) . 'T000000Z';
		$radky[] = 'DTSTART;VALUE=DATE:' . ics_datum($rozsah[0]);
		$radky[] = 'DTEND;VALUE=DATE:' . $konec->format('Ymd');
		$radky[] = 'SUMMARY:FO: ' . $nazev . $kat;
		if (isset($row['misto'])) {
			$radky[] = 'LOCATION:' . $row['misto'];
		}
		$radky[] = 'URL:' . rtrim(BASE_URL, '/') . '/terminy';
		$radky[] = 'END:VEVENT';
	}
}

$radky[] = 'END:VCALENDAR';

Header('Content-Type: text/calendar; charset=utf-8');
echo implode("\r\n", $radky) . "\r\n";
