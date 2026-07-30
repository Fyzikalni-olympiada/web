#!/bin/bash
# Purgne z cache Cloudflare URL souborů změněných tímto nasazením.
# Manifest hashů dist/ z minulého nasazení drží actions/cache (temp/manifest.txt);
# bez něj (první běh, propadlá cache) se purgne všechno.
set -eu

if [ -z "${CLOUDFLARE_ZONE_ID:-}" ]; then
	echo "CLOUDFLARE_ZONE_ID není nastaveno, purge se přeskakuje"
	exit 0
fi

WEB=https://fyzikalniolympiada.cz
MANIFEST=temp/manifest.txt
NOVY=$(mktemp)
(cd dist && find . -type f -print0 | LC_ALL=C sort -z | xargs -0 sha256sum) > "$NOVY"

api() {
	curl -sS -X POST "https://api.cloudflare.com/client/v4/zones/$CLOUDFLARE_ZONE_ID/purge_cache" \
		-H "Authorization: Bearer $CLOUDFLARE_API_TOKEN" \
		-H "Content-Type: application/json" \
		--data "$1" | jq -e '.success' > /dev/null
}

if [ ! -f "$MANIFEST" ]; then
	echo "manifest z minulého nasazení chybí: purge celé cache"
	api '{"purge_everything":true}'
else
	# řádky diffu "< <hash>  ./cesta" -> /cesta; u HTML i URL bez přípony (cleanUrls)
	URLS=$(mktemp)
	diff "$MANIFEST" "$NOVY" | sed -n 's/^[<>] [0-9a-f]*  \.//p' | sort -u | while read -r CESTA; do
		echo "$WEB$CESTA"
		case "$CESTA" in
			/index.html) echo "$WEB/" ;;
			*.html) echo "$WEB${CESTA%.html}" ;;
		esac
	done > "$URLS"
	echo "URL k purgnutí: $(wc -l < "$URLS")"
	if [ -s "$URLS" ]; then
		split -l 30 "$URLS" "$URLS-batch-"
		for DAVKA in "$URLS-batch-"*; do
			api "$(jq -R . "$DAVKA" | jq -cs '{files: .}')"
		done
	fi
fi

mkdir -p temp
mv "$NOVY" "$MANIFEST"
echo "hotovo"
