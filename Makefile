# Statický web Fyzikální olympiády

.PHONY: dev build

# lokální náhled na http://localhost:8000
dev:
	php -S localhost:8000 build/router.php

# sestavení statického webu do dist/ včetně vyhledávacího indexu
build:
	php build/build.php
	npx -y pagefind@1 --site dist
