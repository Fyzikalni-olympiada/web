<?php
/**
 * Veřejný JSON s termíny aktuálního ročníku (/data/terms.json),
 * vedle něj se publikuje i zdrojový YAML (/data/terms.yaml).
 * Používá router (dev) a build skript.
 */

$terminy_json = [];
foreach (data_terms(AKTUALNI_ROCNIK) as $kategorie => $terminy) {
    foreach ($terminy as $t) {
        if (empty($t['date'])) {
            continue;
        }
        $polozka = [
            'kategorie' => $kategorie,
            'nazev' => $t['nazev'],
            'termin' => $t['termin'],
            'date' => $t['date'],
        ];
        if (isset($t['misto'])) {
            $polozka['misto'] = $t['misto'];
        }
        $terminy_json[] = $polozka;
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($terminy_json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
