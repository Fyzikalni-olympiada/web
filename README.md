# fyzikalniolympiada.cz

Statický web Fyzikální olympiády. Obsah žije v souborech v repozitáři
(žádná databáze), web se sestavuje PHP skriptem do `dist/` a nasazuje na
Cloudflare Pages (push do masteru → GitHub Actions).

## Struktura repozitáře

| Co | Kde |
|---|---|
| PHP kód | `src/` – šablona webu (`index.php`), RSS, ICS, `functions/`, `content/` |
| Šablony stránek | `html/*.html` |
| Build a dev server | `build/` (`build.php`, `router.php`, `purge.sh`) |
| Malé assety | `assets/` – `css/`, `js/`, `pic/`, `fonts/`, favicony, `_redirects`, `_headers` |
| Data | `data/*.yaml` + `data/news/` |
| Velký obsah (v URL) | `archiv/`, `texty/`, `tana/`, `dokumenty/`, `vysledky/` |

## Struktura obsahu

| Co | Kde |
|---|---|
| Stránky (routy) | `data/files.yaml` – pathname → soubor, titulek, description |
| Menu | `data/menu.yaml` (`ikona:` odkazuje do `assets/pic/menu/`) |
| Termíny | `data/terms/<ročník>.yaml` – kategorie `spolecne`, `A`, `BCD`, `EF`, `G` |
| Novinky | `data/news/*.html` – YAML hlavička + HTML tělo; obrázek novinky je soubor stejného jména vedle (publikuje se pod `/data/news/`) |
| České úspěchy na MFO | `data/mfo-uspechy.yaml` (počítá se z něj i statistika na `/co-je-fo`) |
| Celostátní kola | `data/celostatni-kola.yaml` |
| Studijní texty | `data/studijni-texty.yaml` |
| Diskuse (archiv) | `data/forum.yaml` (zmrazené) |
| Ročník | `src/config.php` (`AKTUALNI_ROCNIK`, `AKTUALNI_ROK`) |
| Fotogalerie Táni | `tana/photos_<rok>/` – `photos/` + `thumbnails/` (+ `popisky.csv`); rozcestník se generuje sám |

## Postupy

```sh
make dev      # lokální náhled na http://localhost:8000
make build    # sestaví web do dist/ (vč. pagefind indexu, potřebuje npx)
```

**Nová novinka:** přidat soubor `data/news/RRRR-MM-DD-nazev.html` (vzor
vedle), u starých novinek přepnout `homepage: false`. Commit + push →
GitHub Actions web sestaví a nasadí.

**Nový ročník:** upravit `src/config.php`, založit `data/terms/<ročník>.yaml`.

Jediný klientský skript je `assets/js/fo.js` (bez závislostí): věci
závislé na datu (zvýraznění nejbližšího termínu na osách), menu, taby,
accordion, fotogalerie, filtr studijních textů a přesměrování starých
adres s query parametry. Web se proto nemusí přestavovat, když jen
plyne čas. Vyhledávání je Pagefind – index se generuje při buildu.
