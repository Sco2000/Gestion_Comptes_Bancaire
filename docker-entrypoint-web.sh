#!/bin/sh
set -e

echo "🔑 Vérification des clés Passport..."
if [ ! -f "storage/oauth-private.key" ] || [ ! -f "storage/oauth-public.key" ]; then
  php artisan passport:install --force
fi

echo "🔧 Vérification des permissions..."
mkdir -p storage/framework/{cache,data,sessions,testing,views} bootstrap/cache storage/logs
chown -R laravel:laravel storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "🚀 Démarrage du serveur Laravel..."
exec php artisan serve --host=0.0.0.0 --port=8000
