# 🔑 Gestion des clés Passport
if [ ! -f "storage/oauth-private.key" ] || [ ! -f "storage/oauth-public.key" ]; then
  echo "🔑 Génération des clés Passport..."
  php artisan passport:install --force
else
  echo "✅ Clés Passport déjà présentes."
fi

echo "Starting queue workers..."
# Démarrer les workers de queue en arrière-plan
php artisan queue:work --tries=3 --timeout=90 --sleep=3 --max-jobs=1000 --verbose &
echo $! > /tmp/laravel-queue-worker.pid

echo "Starting Laravel application..."
exec "$@"