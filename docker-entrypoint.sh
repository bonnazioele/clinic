#!/bin/sh
set -e

# create storage symlink (safe)
php artisan storage:link || true

# run migrations only when explicitly enabled by env RUN_MIGRATIONS=true
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

# cache config (no-op if APP_KEY missing)
php artisan config:cache || true

exec "$@"