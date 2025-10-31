# Étape 1 : Build des dépendances PHP
FROM composer:2.6 AS composer-build

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Étape 2 : Image finale
FROM php:8.3-fpm-alpine

# Installer les extensions PHP nécessaires et Nginx
RUN apk add --no-cache postgresql-dev nginx supervisor bash \
    && docker-php-ext-install pdo pdo_pgsql

# Créer un utilisateur non-root
RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les dépendances et le code
COPY --from=composer-build /app/vendor ./vendor
COPY . .

# Configurer Nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Créer les répertoires nécessaires et permissions
RUN mkdir -p storage/framework/{cache,data,sessions,testing,views} bootstrap/cache storage/logs \
    && chown -R laravel:laravel storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copier scripts d'entrée
COPY docker-entrypoint-web.sh /usr/local/bin/docker-entrypoint-web.sh
COPY docker-entrypoint-worker.sh /usr/local/bin/docker-entrypoint-worker.sh
RUN chmod +x /usr/local/bin/docker-entrypoint-web.sh /usr/local/bin/docker-entrypoint-worker.sh

# Passer à l'utilisateur non-root
USER laravel

# Exposer le port HTTP standard
EXPOSE 80

# Entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint-web.sh"]
