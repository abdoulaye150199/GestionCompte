#!/bin/bash

# Installer les dépendances Composer
composer install --no-dev --optimize-autoloader

# Générer la clé de l'application si elle n'existe pas
php artisan key:generate --force

# Installer les clés Passport si elles n'existent pas
php artisan passport:keys --force

# Exécuter les migrations
php artisan migrate --force

# Démarrer l'application sur le port spécifié par Render
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}