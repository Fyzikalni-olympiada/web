<?php
/**
 * Vypíše termíny aktuálního ročníku jako JSON pro js/fo.js.
 * Používá router (dev) a build skript (dist/data/terms.json).
 */

$terminy_json = [];
foreach (data_terms(AKTUALNI_ROCNIK) as $kategorie => $terminy) {
    foreach ($terminy as $t) {
        if (empty($t['date'])) {
            continue;
        }
        $terminy_json[] = [
            'kategorie' => $kategorie,
            'nazev' => $t['nazev'],
            'termin' => $t['termin'],
            'date' => $t['date'],
        ];
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($terminy_json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
