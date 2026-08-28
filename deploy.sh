#!/bin/bash
# Run on Hostinger after git pull
set -e
cd "$(dirname "$0")"

mkdir -p storage/framework/views
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "Deploy complete."
