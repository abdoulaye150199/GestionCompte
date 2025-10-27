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
RUN apk add --no-cache postgresql-dev postgresql-client nodejs npm libpq openssl \
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
RUN cp -r vendor/swagger-api/swagger-ui/dist/* public/swagger-assets/

# Définir les permissions
RUN chown -R laravel:laravel /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && chmod -R 755 public/swagger-assets \
    && chmod -R 755 public/vendor/swagger-ui

# Configuration minimale pour le build
COPY .env.example .env

# Changer les permissions du fichier .env pour l'utilisateur laravel
RUN chown laravel:laravel .env

# Préparation de l'application
USER laravel
RUN php artisan storage:link && \
    php artisan view:cache

# Génération de la documentation API uniquement si les variables nécessaires sont présentes
RUN if [ ! -z "$APP_URL" ]; then \
    php artisan l5-swagger:generate; \
    fi

# Copier la documentation générée dans le dossier public
RUN cp storage/api-docs/api-docs.json public/api-docs.json && \
    chmod 644 public/api-docs.json
USER root

# Copier le script d'entrée
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Passer à l'utilisateur non-root
USER laravel

# Exposer le port 8000
EXPOSE 8000

# Commande par défaut
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
