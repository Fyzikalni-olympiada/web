# fyzikalniolympiada.cz

Statický web Fyzikální olympiády. Obsah žije v souborech v repozitáři
(žádná databáze), web se sestavuje PHP skriptem do `dist/` a nasazuje na
Cloudflare Pages (push do masteru → GitHub Actions).

## Struktura obsahu

| Co | Kde |
|---|---|
| Stránky (routy) | `data/files.yaml` – pathname → soubor + titulek |
| Menu | `data/menu.yaml` |
| Termíny | `data/terms/<ročník>.yaml` – kategorie `spolecne`, `A`, `BCD`, `EF`, `G` |
| Novinky | `data/news/*.html` – YAML hlavička + HTML tělo |
| Diskuse (archiv) | `data/forum.yaml` (zmrazené) |
| Ročník | `config.php` (`AKTUALNI_ROCNIK`, `AKTUALNI_ROK`) |
| Obsahové stránky | `html/*.html`, `archiv/`, `texty/`, … |

## Postupy

```sh
make dev      # lokální náhled na http://localhost:8000
make build    # sestaví web do dist/
```

**Nová novinka:** přidat soubor `data/news/RRRR-MM-DD-nazev.html` (vzor
vedle), u starých novinek přepnout `homepage: false`. Commit + push →
GitHub Actions web sestaví a nasadí.

**Nový ročník:** upravit `config.php`, založit `data/terms/<ročník>.yaml`.

Klientský JS (`js/fo.js`) řeší věci závislé na datu – „Nejbližší termíny"
v bočním panelu (z `/data/terms.json`), zvýraznění na `/terminy`, náhodnou
fotku (z `/data/thumbs.json`) a přesměrování starých adres s query
parametry. Web se proto nemusí přestavovat, když jen plyne čas.
