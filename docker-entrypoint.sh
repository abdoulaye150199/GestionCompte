#!/bin/sh

# Attendre que la base de données soit prête
echo "Waiting for database to be ready..."
while ! pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do
  echo "Database is unavailable - sleeping"
  sleep 1
done

echo "Database is up - executing migrations"
php artisan migrate --force

echo "Installing Passport keys..."
php artisan passport:install --force

echo "Generating and publishing Swagger documentation..."
php artisan swagger:publish

echo "Starting Laravel application..."
exec "$@"