#!/bin/sh

echo "Starting Laravel application..."

# Clear any existing caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Try to connect to database and run setup if possible
echo "Checking database connection..."
if php artisan migrate:status > /dev/null 2>&1; then
    echo "Database is accessible, running setup..."

    # Run migrations
    echo "Running migrations..."
    php artisan migrate --force

    # Generate Passport keys if they don't exist
    if [ ! -f storage/oauth-private.key ] || [ ! -f storage/oauth-public.key ]; then
        echo "Generating Passport keys..."
        php artisan passport:keys --force
    fi

    # Create personal access client if it doesn't exist
    if ! php artisan passport:client --no-interaction 2>&1 | grep -q "Personal access client created successfully"; then
        echo "Creating personal access client..."
        php artisan passport:client --personal --no-interaction
    fi

    # Cache configuration
    echo "Caching configuration..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    echo "Database not accessible, skipping database operations..."
    echo "Application will start without database caching"
fi

# Start the application
echo "Starting application..."
exec "$@"