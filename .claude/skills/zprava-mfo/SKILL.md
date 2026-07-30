---
name: zprava-mfo
description: Vytvoří zprávu o ročníku Mezinárodní fyzikální olympiády (MFO/IPhO) pro web FO včetně loga, výsledků, programu, zapojení do menu a odkazů. Použij, když uživatel chce „zprávu o N. MFO", doplnit chybějící ročník MFO na web apod.
---

# Zpráva o ročníku MFO

Vyrobí stránku `/mfo/<N>` podle vzoru existujících zpráv (viz `archiv/mfo/53.html`,
podrobnější starší vzor `archiv/mfo/52.html`) a zapojí ji do webu.

## Vstupy

- číslo ročníku MFO `<N>` a rok konání (`rok = 1970 + N` u novodobých ročníků — ověř!)
- ročník FO, ze kterého se vybíralo družstvo: `ročník FO = rok − 1959`
  (např. MFO 2023 ← ústřední kolo 64. ročníku FO)
- doprovod delegace: obvykle doc. RNDr. Jan Kříž, Ph.D. (vedoucí) a RNDr. Filip
  Studnička, Ph.D. (zástupce), oba Univerzita Hradec Králové — **ověř u uživatele**
- volitelně fotka družstva → `archiv/mfo/<N>/druzstvo.jpg` + popisek se jmény zleva

## Sběr faktů (nic si nedomýšlet, vše z dohledatelných zdrojů)

1. **Oficiální web ročníku** — najdi přes web search; agregátor
   `https://ipho-unofficial.org/timeline/<rok>/` má datum, místo, počty účastníků
   a medailové statistiky (zlaté/stříbrné/bronzové/čestná uznání).
2. **Výsledky českých soutěžících** — `https://ipho-unofficial.org/countries/CZE/individual`
   (jméno, ocenění, absolutní pořadí). **Pokud pro daný rok žádné záznamy nejsou,
   ČR se ročníku nezúčastnila** (např. 54. MFO 2024 v Íránu) — domluv se s uživatelem;
   obvykle pak vzniká jen krátká faktická stránka bez delegace a výsledků
   (vzor `archiv/mfo/54.html`).
3. **Školy soutěžících** — výsledková listina ústředního kola kategorie A příslušného
   ročníku FO: `https://osmo.fyzikalniolympiada.cz/public/kolo/<ročníkFO>-A-3/vysledky`
   (rozcestník `https://osmo.fyzikalniolympiada.cz/public/history/`). Zkratku „G" rozepiš
   na „Gymnázium". Zkontroluj, že všech 5 jmen z MFO sedí na výsledkovku.
4. **Program** — oficiální web ročníku; nejspolehlivější bývá timetable v PDF cirkuláři
   („Second Circular"). Zjisti: den zahájení, dny a pořadí zkoušek (teoretická 3 úlohy,
   experimentální 2, každá 5 hodin), exkurze, zakončení.
5. **Rozbor úloh** — zadání bývají na oficiálním webu ročníku (sekce Theoretical /
   Experimental exam) nebo na `ipho-new.org`; zjisti témata jednotlivých úloh.
6. **Logo** — stáhni z oficiálního webu v nejvyšším dostupném rozlišení
   → `pic/mfo/<N>.png`; vizuálně zkontroluj (Read).

## Obsah stránky `archiv/mfo/<N>.html`

Stručné a informativní, žádné vlastní úvahy a hodnocení. Struktura:

1. `<h2>Zpráva o konání <N>. Mezinárodní fyzikální olympiády</h2>`,
   `<h3><místo>, <země>, <rok></h3>`, logo `<img src="/pic/mfo/<N>.png" … align="right"
   style="max-width: 50%">`
2. odstavec: kdy a kde se konala, místo konání, počet soutěžících a zemí
3. delegace: `<ul>` doprovod, pak `<ul>` soutěžící kurzívou se školami
4. věta o výběru (ústřední kolo ročníku FO + výběrové soustředění) a přípravě (UHK)
5. `<h3>Program soutěže</h3>` — jeden odstavec: ceremonie, dny zkoušek, exkurze
6. `<h3>Úlohy soutěže</h3>` — **krátký odstavec s rozborem úloh**: témata tří
   teoretických a dvou experimentálních úloh (jedna dvě věty ke každé)
7. `<h3>Výsledky</h3>` — medailové statistiky ročníku a `<ul>` českých soutěžících
   tučně, seřazených podle pořadí: ocenění + absolutní místo
8. odkaz na oficiální stránky ročníku
9. volitelně fotka družstva (`<div class="center-box">`, vzor v 52.html)

## Zapojení do webu

- `data/files.yaml`: routa `mfo/<N>` → `archiv/mfo/<N>.html`,
  title „<N>. Mezinárodní fyzikální olympiáda" (klíče drž abecedně)
- `data/menu.yaml`: do children „Mezinárodní soutěž" položku
  „<N>. MFO, <země>" jako **první za „České úspěchy"**
- `html/mfo_odkazy.html`: `<li>` s odkazem na oficiální web a logem (width 200),
  nad předchozí ročník

## Ověření a commit

1. `make build` bez chyb; dev server (`make dev`): `/mfo/<N>` renderuje, jména
   sedí, menu i stránka odkazů položku mají, logo se servíruje
2. vizuální kontrola stránky v prohlížeči
3. commit s one-line zprávou: `zpráva o <N>. MFO (<místo> <rok>)`
