#!/bin/bash

echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

if [ -f storage/api-docs/api-docs.json ]; then
    # Replace HTTP with HTTPS in the JSON file
    sed -i 's|http://|https://|g' storage/api-docs/api-docs.json
    
    # Copy to public directory
    cp storage/api-docs/api-docs.json public/api-docs.json
    chmod 644 public/api-docs.json
    
    echo "✓ Documentation generated and copied to public directory"
    
    # Verify HTTPS
    if grep -q "https://" public/api-docs.json; then
        echo "✓ HTTPS URLs configured correctly"
    else
        echo "⚠ WARNING: No HTTPS URLs found in api-docs.json"
    fi
else
    echo "✗ Failed to generate Swagger documentation"
    exit 1
fi