#!/bin/sh
set -e

# Vérifier les clés Passport
if [ ! -f "storage/oauth-private.key" ] || [ ! -f "storage/oauth-public.key" ]; then
  php artisan passport:install --force
fi

# Vérifier les permissions
mkdir -p storage/framework/{cache,data,sessions,testing,views} bootstrap/cache storage/logs
chown -R laravel:laravel storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Démarrer PHP-FPM et Nginx via Supervisor
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
