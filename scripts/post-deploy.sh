#!/bin/bash

# Création des dossiers nécessaires
mkdir -p public/swagger-assets
mkdir -p public/vendor/swagger-ui

# Copie des assets Swagger
cp -r vendor/swagger-api/swagger-ui/dist/* public/swagger-assets/
cp -r vendor/swagger-api/swagger-ui/dist/* public/vendor/swagger-ui/

# Copie du fichier de documentation API
cp storage/api-docs/api-docs.json public/api-docs.json

# Donner les permissions appropriées
chmod -R 755 public/swagger-assets
chmod -R 755 public/vendor/swagger-ui
chmod 644 public/api-docs.json

# Nettoyer le cache
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear

# Régénérer la documentation Swagger
php artisan l5-swagger:generate