#!/usr/bin/env bash
# Run final optimization on production only

echo "🚀 Running final tasks..."
echo "🔗 Linking storage..."
run_php artisan storage:link

echo "📦 Building frontend assets..."
run_pkg_manager run build
