#!/usr/bin/env bash
# Run final optimization on production only

if $IS_PRODUCTION; then
    echo "🚀 Optimizing application..."

    run_php artisan optimize
fi
