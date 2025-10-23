#!/usr/bin/env bash
# exit on error
set -o errexit

echo "🚀 Starting build process..."

# Ensure we have all required environment variables
if [ -z "$DB_HOST" ] || [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ]; then
    echo "❌ Missing required database environment variables"
    exit 1
fi

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Generate key if not already set
echo "🔑 Checking application key..."
if [ -z "$APP_KEY" ]; then
    echo "Generating new application key..."
    php artisan key:generate --force
fi

# Create storage directory structure
echo "📁 Setting up storage directory..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs

# Set proper permissions
echo "🔒 Setting proper permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Create symbolic link for storage
echo "🔗 Creating storage link..."
php artisan storage:link

# Clear and cache configuration
echo "🧹 Clearing and caching configuration..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Run database migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

echo "✅ Build process completed!"