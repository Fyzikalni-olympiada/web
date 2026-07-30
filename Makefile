# Statický web Fyzikální olympiády

.PHONY: dev build deploy

# lokální náhled na http://localhost:8000
dev:
	php -S localhost:8000 build/router.php

# sestavení statického webu do dist/
build:
	php build/build.php

# ruční nasazení (běžně nasazuje GitHub Actions po pushi);
# nižší souběžnost uploadu, jinak se na pomalé lince požadavky vyhladoví a timeoutují
deploy: build
	FIREBASE_HOSTING_UPLOAD_CONCURRENCY=10 npx firebase-tools deploy --only hosting
