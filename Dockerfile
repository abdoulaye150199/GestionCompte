# Étape 1: Build des dépendances PHP
FROM composer:2.6 AS composer-build

WORKDIR /app

# Copier les fichiers de dépendances
COPY composer.json composer.lock ./

# Installer les dépendances PHP sans scripts post-install
# Ignorer la vérification de l'extension mongodb pendant le stage de build
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts --ignore-platform-req=ext-mongodb

# Étape 2: Image finale pour l'application (Alpine stable)
FROM php:8.3-fpm-alpine3.19

# Installer les dépendences système nécessaires pour Postgres et pour construire des extensions PECL
RUN set -eux; \
    apk update && apk upgrade --no-cache; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        autoconf \
        gcc \
        musl-dev \
        linux-headers \
        openssl-dev \
        zlib-dev \
        libzip-dev \
        build-base; \
# install runtime packages (include libzip runtime so extension can find libzip at runtime)
    apk add --no-cache postgresql-dev postgresql-client ca-certificates tzdata bash tini libzip; \
# Installer les extensions PDO et zip
    docker-php-ext-install -j"$(nproc)" pdo pdo_pgsql zip || true

# Installer l'extension mongodb via pecl (nécessite build deps)
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Nettoyer les paquets de build pour alléger l'image
RUN apk del .build-deps || true; rm -rf /tmp/pear ~/.pearrc /var/cache/apk/*

# Créer un utilisateur non-root
RUN addgroup -g 1000 laravel && adduser -G laravel -u 1000 -D laravel

WORKDIR /var/www/html

# Copier les dépendances installées depuis l'étape de build
COPY --from=composer-build /app/vendor ./vendor

# Copier le reste du code de l'application
COPY . .

# Créer les répertoires nécessaires et définir les permissions
RUN mkdir -p storage/framework/{cache,data,sessions,testing,views} \
    && mkdir -p storage/logs bootstrap/cache \
    && chown -R laravel:laravel /var/www/html || true \
    && chmod -R 775 storage bootstrap/cache || true

# Copier le script d'entrée (si présent)
COPY docker-entrypoint.sh /usr/local/bin/
RUN if [ -f /usr/local/bin/docker-entrypoint.sh ]; then chmod +x /usr/local/bin/docker-entrypoint.sh; fi

# Passer à l'utilisateur non-root
USER laravel

# Expose (document) a default port; the container will bind to $PORT at runtime
EXPOSE 8000

# Commande par défaut: respectez la variable d'environnement $PORT fournie par Render
# If PORT is not set, fall back to 8000 for local dev
CMD ["sh", "-lc", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
