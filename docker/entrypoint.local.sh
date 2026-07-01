#!/bin/sh
set -e

# Ensure storage directories exist and have proper permissions
mkdir -p /var/www/storage/app/public/assets /var/www/storage/framework/cache/data /var/www/storage/framework/sessions /var/www/storage/framework/testing /var/www/storage/framework/views /var/www/storage/logs
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create storage link if it doesn't exist
if [ ! -L /var/www/public/storage ]; then
    echo "Creating public storage link..."
    php artisan storage:link --force
fi

# Clean up legacy Vite hot file to prevent asset connection refused issues
if [ -f /var/www/public/hot ]; then
    echo "Removing legacy public/hot file..."
    rm -f /var/www/public/hot
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

# Re-ensure correct ownership and permissions before starting the service
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public/assets
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Execute CMD passed from Docker
exec "$@"
