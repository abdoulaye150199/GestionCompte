# Use the official PHP image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy and enable Apache configuration
COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default.conf

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure Apache Document Root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy composer files first
COPY composer.json composer.lock ./

# Install composer dependencies
RUN composer install --no-scripts --no-autoloader --no-dev

# Copy project files
COPY . .

# Create .env file from example
RUN cp .env.example .env \
    && sed -i 's/APP_ENV=.*/APP_ENV=production/' .env \
    && sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env \
    && sed -i 's#DB_CONNECTION=.*#DB_CONNECTION=pgsql#' .env \
    && sed -i 's#DB_HOST=.*#DB_HOST=${DB_HOST}#' .env \
    && sed -i 's#DB_PORT=.*#DB_PORT=${DB_PORT}#' .env \
    && sed -i 's#DB_DATABASE=.*#DB_DATABASE=${DB_DATABASE}#' .env \
    && sed -i 's#DB_USERNAME=.*#DB_USERNAME=${DB_USERNAME}#' .env \
    && sed -i 's#DB_PASSWORD=.*#DB_PASSWORD=${DB_PASSWORD}#' .env

# Optimize composer autoloader
RUN composer dump-autoload --optimize

# Set directory structure and permissions
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Generate application key and optimize
RUN php artisan key:generate --force \
    && php artisan storage:link \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Set proper Apache environment
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Add and configure entry point script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

# Create .env file
RUN touch .env

# Generate optimized autoload files
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]