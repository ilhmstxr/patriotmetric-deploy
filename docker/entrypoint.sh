#!/bin/sh
set -e

# Ensure storage directories exist and have proper permissions
mkdir -p /var/www/storage/app/public /var/www/storage/framework/cache/data /var/www/storage/framework/sessions /var/www/storage/framework/testing /var/www/storage/framework/views /var/www/storage/logs
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create storage link if it doesn't exist
if [ ! -L /var/www/public/storage ]; then
    echo "Creating public storage link..."
    php artisan storage:link --force
fi

# Run optimizations depending on APP_ENV
if [ "$APP_ENV" = "production" ]; then
    echo "Optimizing Laravel configuration and routes for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    echo "Clearing configuration caches for development mode..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
fi

# Execute CMD passed from Docker
exec "$@"
