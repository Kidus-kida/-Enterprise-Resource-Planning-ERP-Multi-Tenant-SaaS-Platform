#!/bin/sh

set -e

echo "Running database migrations..."

php artisan migrate --force

echo "Clearing Laravel caches..."

php artisan optimize:clear

echo "Starting Laravel application..."

php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
