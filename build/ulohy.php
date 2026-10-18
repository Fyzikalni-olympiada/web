<?php
/**
 * Kódy a názvy úloh vyparsované přes pdftotext ze zadání v archivu
 * (experimentální zadání _pz se přeskakují – nemají jednotný formát).
 * Publikuje se jako /data/ulohy.json a /data/ulohy.yaml.
 */

function ulohy_rocniku($rocnik)
{
    if (!shell_exec('command -v pdftotext')) {
        trigger_error('chybí pdftotext (poppler-utils)', E_USER_ERROR);
    }
    $ulohy = [];
    foreach (glob(ROOT_DIR . 'files/archiv/' . $rocnik . '/fo' . $rocnik . '*_z.pdf') as $pdf) {
        if (substr($pdf, -7) === '_pz.pdf') {
            continue;
        }
        if (!preg_match('~fo' . $rocnik . '([a-z]+[0-9])_z\.pdf$~', $pdf, $m)) {
            continue;
        }
        $kolo = strtoupper($m[1]);
        $radky = [];
        exec('pdftotext ' . escapeshellarg($pdf) . ' - 2>/dev/null', $radky);

        /* EF a G uvádějí kódy přímo (FO67EF1-1: Název, občas bez dvojtečky,
         * s poznámkou v závorce či s názvem až na dalším řádku), jinde jsou
         * úlohy číslované od jedničky (1. Název) */
        $nalezene = [];
        foreach ($radky as $i => $radek) {
            $experimentalni = strpos($radek, '(experimentální úloha)') !== false;
            $radek = trim(str_replace('(experimentální úloha)', '', $radek), " \t\n\r\0\x0B\x0C");
            if (preg_match('~^FO' . $rocnik . $kolo . '-(\d+)\s*:?\s*(.*)$~u', $radek, $u)) {
                $nazev = trim($u[2]);
                for ($j = $i + 1; $nazev === '' && $j < count($radky); $j++) {
                    $nazev = trim($radky[$j], " \t\n\r\0\x0B\x0C");
                }
                $nalezene[(int) $u[1]] = [$nazev, $experimentalni];
            }
        }
        if (!$nalezene) {
            $cislo = 1;
            foreach ($radky as $radek) {
                if (preg_match('~^(\d+)\.\s+(\p{Lu}.{0,80})$~u', trim($radek, " \t\n\r\0\x0B\x0C"), $u)
                        && (int) $u[1] === $cislo) {
                    $nalezene[$cislo++] = [trim($u[2]), false];
                }
            }
        }
        foreach ($nalezene as $cislo => [$nazev, $experimentalni]) {
            /* praktické úlohy kategorií A–D značí prefix v názvu, autor za názvem do exportu nepatří */
            if (preg_match('~^(?:Praktická|Experimentální) úloha:\s*(.+)$~u', $nazev, $u)) {
                $nazev = $u[1];
                $experimentalni = true;
            }
            $nazev = preg_replace('~\s+\p{Lu}\.\s?\p{Lu}\p{L}+$~u', '', $nazev);
            $ulohy[] = ['kod' => 'FO' . $rocnik . $kolo . '-' . $cislo, 'nazev' => $nazev,
                        'experimentalni' => $experimentalni];
        }
    }
    return $ulohy;
}



function ulohy_yaml($ulohy)
{
    return $ulohy ? Symfony\Component\Yaml\Yaml::dump($ulohy) : "[]\n";
}
