#!/bin/bash
# Run on Hostinger after git pull
set -e
cd "$(dirname "$0")"

echo "==> Syncing latest code (run before this if needed: git pull origin main)"

mkdir -p storage/framework/views
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/storage/products
mkdir -p public/storage/groups
mkdir -p public/storage/reviews
mkdir -p public/storage/branding
mkdir -p public/storage/reports
mkdir -p public/storage/projects
mkdir -p public/storage/study-plans
mkdir -p public/storage/offers

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

composer install --no-dev --optimize-autoloader --no-interaction

# Rebuild frontend when Node is available; otherwise use committed build/ from git
if command -v npm >/dev/null 2>&1; then
  npm ci --no-audit --no-fund
  npm run build
  cp -r public/build/. build/
else
  echo "npm not found — using build/ assets from git"
  if [ -d public/build ] && [ -f public/build/manifest.json ]; then
    cp -r public/build/. build/
  fi
fi

php artisan migrate --force
php artisan optimize:clear
php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo \"OPcache cleared\n\"; }"

echo "Deploy complete."
echo "Verify: curl -s https://smartflowuae.com/api/products | head -c 400"
