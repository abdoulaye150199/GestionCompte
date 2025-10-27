# Étape 1: Build des dépendances PHP
FROM composer:2.6 AS composer-build

WORKDIR /app

# Copier les fichiers de dépendances
COPY composer.json composer.lock ./

# Installer les dépendances PHP sans scripts post-install
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Étape 2: Image finale pour l'application
FROM php:8.3-fpm-alpine

# Installer les extensions PHP nécessaires et les outils
RUN apk add --no-cache postgresql-dev nodejs npm libpq openssl \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Créer un utilisateur non-root
RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les dépendances installées depuis l'étape de build
COPY --from=composer-build /app/vendor ./vendor

# Copier le reste du code de l'application
COPY . .

# Créer les répertoires nécessaires
RUN mkdir -p storage/framework/{cache,data,sessions,testing,views} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && mkdir -p public/swagger-assets \
    && mkdir -p public/vendor/swagger-ui

# Copier les assets Swagger
RUN cp -r vendor/swagger-api/swagger-ui/dist/* public/swagger-assets/ \
    && cp -r vendor/swagger-api/swagger-ui/dist/* public/vendor/swagger-ui/

# Définir les permissions
RUN chown -R laravel:laravel /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && chmod -R 755 public/swagger-assets \
    && chmod -R 755 public/vendor/swagger-ui

# Créer un fichier .env minimal pour le build
RUN echo "APP_NAME=GestionCompte" > .env && \
    echo "APP_ENV=production" >> .env && \
    echo "APP_KEY=base64:$(openssl rand -base64 32)" >> .env && \
    echo "APP_DEBUG=false" >> .env && \
    echo "APP_URL=https://gestioncompte-api.onrender.com" >> .env && \
    echo "" >> .env && \
    echo "LOG_CHANNEL=stack" >> .env && \
    echo "LOG_LEVEL=error" >> .env && \
    echo "" >> .env && \
    echo "DB_CONNECTION=pgsql" >> .env && \
    echo "DB_HOST=ep-cold-flower-ahmlgg4s-pooler.c-3.us-east-1.aws.neon.tech" >> .env && \
    echo "DB_PORT=5432" >> .env && \
    echo "DB_DATABASE=neondb" >> .env && \
    echo "DB_USERNAME=neondb_owner" >> .env && \
    echo "DB_PASSWORD=npg_nmGJz3oHRWV1" >> .env && \
    echo "DB_SCHEMA=public" >> .env && \
    echo "DB_SSLMODE=require" >> .env && \
    echo "" >> .env && \
    echo "# Neon Database for archiving" >> .env && \
    echo "NEON_DATABASE_URL=postgresql://neondb_owner:npg_nmGJz3oHRWV1@ep-cold-flower-ahmlgg4s-pooler.c-3.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require" >> .env && \
    echo "NEON_DB_HOST=ep-cold-flower-ahmlgg4s-pooler.c-3.us-east-1.aws.neon.tech" >> .env && \
    echo "NEON_DB_PORT=5432" >> .env && \
    echo "NEON_DB_DATABASE=neondb" >> .env && \
    echo "NEON_DB_USERNAME=neondb_owner" >> .env && \
    echo "NEON_DB_PASSWORD=npg_nmGJz3oHRWV1" >> .env && \
    echo "" >> .env && \
    echo "CACHE_DRIVER=file" >> .env && \
    echo "SESSION_DRIVER=file" >> .env && \
    echo "QUEUE_CONNECTION=sync" >> .env && \
    echo "" >> .env && \
    echo "L5_SWAGGER_GENERATE_ALWAYS=true" >> .env && \
    echo "L5_SWAGGER_CONST_HOST=https://gestioncompte-api.onrender.com" >> .env

# Changer les permissions du fichier .env pour l'utilisateur laravel
RUN chown laravel:laravel .env

# Générer la clé d'application et optimiser
USER laravel
RUN php artisan key:generate --force && \
    php artisan passport:install --force && \
    php artisan storage:link && \
    php artisan l5-swagger:generate && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan optimize

# Copier la documentation générée dans le dossier public
RUN cp storage/api-docs/api-docs.json public/api-docs.json && \
    chmod 644 public/api-docs.json && \
    php artisan passport:install --force
USER root

# Copier le script d'entrée
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Passer à l'utilisateur non-root
USER laravel

# Exposer le port 8000
EXPOSE 8000

# Commande par défaut
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
