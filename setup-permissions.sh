#!/bin/bash

# Create storage structure if it doesn't exist
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/logs

# Set correct permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 755 public

# Make sure files are owned by www-data (Apache user)
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
chown -R www-data:www-data public