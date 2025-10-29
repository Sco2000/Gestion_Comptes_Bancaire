#!/bin/sh

# Attendre que la base de données soit prête
echo "Waiting for database to be ready..."
while ! pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do
  echo "Database is unavailable - sleeping"
  sleep 1
done

echo "Database is up - executing migrations"
php artisan migrate --force

echo "Starting queue workers..."
# Démarrer les workers de queue en arrière-plan
php artisan queue:work --tries=3 --timeout=90 --sleep=3 --max-jobs=1000 --verbose &
echo $! > /tmp/laravel-queue-worker.pid

echo "Starting Laravel application..."
exec "$@"