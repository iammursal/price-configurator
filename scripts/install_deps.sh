#!/usr/bin/env bash
# Install composer dependencies and clear caches

echo "📦 Installing js dependencies..."
run_pkg_manager install

echo "📦 Installing composer dependencies..."
if $IS_PRODUCTION; then
    run_composer install --no-interaction --prefer-dist --optimize-autoloader
else
    run_composer install
fi

echo "🧹 Clearing caches..."
run_php artisan optimize:clear
