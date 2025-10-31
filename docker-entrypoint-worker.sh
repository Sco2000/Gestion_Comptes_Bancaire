#!/bin/sh

echo "Starting queue workers..."
php artisan queue:work --tries=3 --timeout=90 --sleep=3 --max-jobs=1000 --verbose
