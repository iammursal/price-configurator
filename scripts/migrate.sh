#!/usr/bin/env bash
# Run migrations and fresh install tasks

echo "🗄️ Running migrations..."
run_php artisan migrate --force

if $FRESH_INSTALLATION; then
    echo "🆕 Running fresh install tasks..."

    run_php artisan key:generate
    run_php artisan db:seed --force
fi
