#!/bin/sh
# Prepares the container on every start: migrations, catalog and caches.
# Set SEED_DEMO=true to load the fictitious demo clinic and
# SKIP_MIGRATIONS=true on secondary containers such as the scheduler.
set -eu

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is required; generate one with: make env" >&2
    exit 1
fi

if [ "${SKIP_MIGRATIONS:-false}" != "true" ]; then
    php artisan migrate --force
    php artisan db:seed --force
    if [ "${SEED_DEMO:-false}" = "true" ]; then
        php artisan db:seed --class=DemoSeeder --force
    fi
fi
php artisan optimize

exec "$@"
