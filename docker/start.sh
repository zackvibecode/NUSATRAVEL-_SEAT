#!/usr/bin/env bash
set -e

cd /app

echo "==> Bootstrapping SeatWeb"

if [ -z "${APP_KEY:-}" ]; then
  echo "==> APP_KEY missing — generating one for this instance"
  export APP_KEY="$(php artisan key:generate --show --no-ansi)"
fi

echo "==> APP_KEY is set"

# Neon / Render give DATABASE_URL; Laravel reads DB_URL
if [ -n "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
  export DB_URL="$DATABASE_URL"
fi

if [ -n "${DB_URL:-}" ]; then
  export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
  echo "==> Using Postgres (Neon/Render)"
else
  echo "==> Using default DB_CONNECTION=${DB_CONNECTION:-sqlite}"
fi

echo "==> Checking assets"
ls -la public/build/manifest.json

php artisan migrate --force
php artisan db:seed --force

php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "==> Starting server on port ${PORT:-10000}"
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
