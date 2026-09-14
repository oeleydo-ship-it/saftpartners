#!/usr/bin/env bash
# Production deployment for SAF Partners. Run from the project root on the server.
set -euo pipefail

php artisan down --retry=60 || true
trap 'php artisan up' EXIT

composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

php artisan migrate --force
# Idempotent: only inserts missing default content and the initial admin account.
php artisan db:seed --force

php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
