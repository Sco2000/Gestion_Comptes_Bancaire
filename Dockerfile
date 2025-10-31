# Étape 1 : Build des dépendances PHP
FROM composer:2.6 AS composer-build

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Étape 2 : Image finale pour l'application
FROM php:8.3-cli-alpine

# Installer les extensions PHP nécessaires
RUN apk add --no-cache postgresql-dev bash \
    && docker-php-ext-install pdo pdo_pgsql

# Créer un utilisateur non-root
RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les dépendances et le code
COPY --from=composer-build /app/vendor ./vendor
COPY . .

# Créer les répertoires nécessaires et définir les permissions
RUN mkdir -p storage/framework/{cache,data,sessions,testing,views} bootstrap/cache storage/logs \
    && chown -R laravel:laravel storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copier les scripts d'entrée
COPY docker-entrypoint-web.sh /usr/local/bin/docker-entrypoint-web.sh
COPY docker-entrypoint-worker.sh /usr/local/bin/docker-entrypoint-worker.sh
RUN chmod +x /usr/local/bin/docker-entrypoint-web.sh /usr/local/bin/docker-entrypoint-worker.sh

# Passer à l'utilisateur non-root
USER laravel

# Exposer le port de l'application Laravel
EXPOSE 8000

# Entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint-web.sh"]
