#!/bin/sh

# Attendre que la base de données soit prête (si nécessaire)
echo "Waiting for database connection..."
sleep 5

# Exécuter les migrations en production
php artisan migrate --force

# Démarrer Apache en arrière-plan
apache2-foreground