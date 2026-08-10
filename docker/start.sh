#!/usr/bin/env bash
set -e

cd /app

echo "Checking Vite manifest..."
test -f public/build/manifest.json

php artisan migrate --force
php artisan db:seed --force

# Avoid route:cache â€” safer for first deploys
php artisan config:clear
php artisan route:clear
php artisan view:clear

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
