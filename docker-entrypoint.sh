#!/bin/bash

# Fonction pour attendre que la base de données soit prête
wait_for_db() {
    echo "Waiting for database to be ready..."
    
    # Boucle jusqu'à ce que la connexion soit établie
    until php artisan db:monitor 2>&1 | grep -q "successful"; do
        echo "Database is unavailable - waiting..."
        sleep 2
    done
    
    echo "Database is ready!"
}

# S'assurer que les variables d'environnement sont définies
if [ -z "$DB_HOST" ] || [ -z "$DB_PORT" ] || [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ]; then
    echo "Error: Database environment variables are not set"
    exit 1
fi

# Attendre que la base de données soit prête
wait_for_db

# Exécuter les migrations
echo "Running database migrations..."
php artisan migrate --force

# Démarrer Apache
echo "Starting Apache..."
apache2-foreground