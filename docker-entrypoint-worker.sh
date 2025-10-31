#!/bin/sh
set -e

echo "🔧 Vérification des permissions..."
mkdir -p storage/framework/{cache,data,sessions,testing,views} bootstrap/cache storage/logs
chown -R laravel:laravel storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "🚀 Démarrage des workers Laravel Queue..."
exec php artisan queue:work --tries=3 --timeout=90 --sleep=3 --max-jobs=1000 --verbose
