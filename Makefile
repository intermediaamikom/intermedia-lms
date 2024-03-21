.PHONY: all

all: composer-install npm-build cache

composer-install:
	composer install

npm-build:
	npm install
	npm run build

cache:
    php artisan optimize:clear
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
    php artisan icons:cache
    php artisan filament:cache-components
    php artisan telescope:publish
