.PHONY: all

all: git-pull composer-install npm-build cache

git-pull:
	git pull origin main

composer-install:
	composer install

npm-build:
	npm install
	npm run build

cache:
    php artisan horizon:terminate
    php artisan optimize:clear
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
    php artisan icons:cache
    php artisan filament:cache-components
