#!/bin/bash
cd /var/www/lms.intermediaamikom.org

echo "Restoring .env and storage..."
cp /tmp/env_backup /var/www/lms.intermediaamikom.org/.env
cp -r /tmp/storage_backup /var/www/lms.intermediaamikom.org/storage

# Set permissions
chown -R www-data:www-data /var/www/lms.intermediaamikom.org
chmod -R 775 /var/www/lms.intermediaamikom.org/storage

# Install dependencies
composer install --no-dev --optimize-autoloader

# Link public storage
php artisan storage:link