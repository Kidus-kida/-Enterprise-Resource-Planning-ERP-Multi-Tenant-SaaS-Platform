#!/bin/sh

set -e

if [ -n "$RENDER_EXTERNAL_URL" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
elif [ -n "$RENDER_EXTERNAL_HOSTNAME" ]; then
  export APP_URL="https://$RENDER_EXTERNAL_HOSTNAME"
fi

if [ -n "$APP_URL" ] && echo "$APP_URL" | grep -q '^https://'; then
  export SESSION_SECURE_COOKIE=true
fi

if [ -n "$APP_ENV" ] && [ "$APP_ENV" = "production" ]; then
  export APP_DEBUG=false
fi

echo "Running database migrations..."

php artisan migrate --force

echo "Clearing Laravel caches..."

php artisan optimize:clear

echo "Starting Laravel application..."

php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
