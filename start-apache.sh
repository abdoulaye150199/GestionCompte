#!/bin/bash

# Vérifier que le DocumentRoot existe
if [ ! -d "/var/www/html/public" ]; then
    echo "Error: DocumentRoot /var/www/html/public does not exist"
    exit 1
fi

# Vérifier les permissions
if [ ! -w "/var/www/html/storage" ]; then
    echo "Error: storage directory is not writable"
    exit 1
fi

if [ ! -w "/var/www/html/bootstrap/cache" ]; then
    echo "Error: bootstrap/cache directory is not writable"
    exit 1
fi

# Vérifier la configuration Apache
apache2ctl configtest

# Si tout est OK, démarrer Apache
apache2-foreground