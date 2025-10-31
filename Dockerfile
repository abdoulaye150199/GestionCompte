# Étape 1: Build des dépendances PHP
FROM composer:2.6 AS composer-build

WORKDIR /app

# Copier les fichiers de dépendances
COPY composer.json composer.lock ./

# Installer les dépendances PHP sans scripts post-install
# Ignorer la vérification de l'extension mongodb pendant le stage de build
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts --ignore-platform-req=ext-mongodb

# Étape 2: Image finale pour l'application
FROM php:8.3-fpm-alpine

# Installer les dépendances système nécessaires pour Postgres et pour construire des extensions PECL
RUN apk add --no-cache \
    postgresql-dev postgresql-client \
    $PHPIZE_DEPS \
    openssl-dev \
    zlib-dev \
    libzip-dev \
    build-base

# Installer pdo_pgsql et préparer pecl
RUN docker-php-ext-install pdo pdo_pgsql

# Installer l'extension mongodb via pecl
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb \
    # nettoyer les paquets de build pour alléger l'image
    && apk del build-base $PHPIZE_DEPS openssl-dev zlib-dev libzip-dev

# Créer un utilisateur non-root
RUN addgroup -g 1000 laravel && adduser -G laravel -u 1000 -D laravel

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les dépendances installées depuis l'étape de build
COPY --from=composer-build /app/vendor ./vendor

# Copier le reste du code de l'application
COPY . .

# Créer les répertoires nécessaires et définir les permissions
RUN mkdir -p storage/framework/{cache,data,sessions,testing,views} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R laravel:laravel /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Copier le script d'entrée
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Passer à l'utilisateur non-root
USER laravel

# Exposer le port 8000
EXPOSE 8000

# Commande par défaut
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
