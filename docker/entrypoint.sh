#!/bin/sh
set -e

# Ensure storage directories exist and have proper permissions
mkdir -p /opt/patriotmetric/storage/app/public /opt/patriotmetric/storage/framework/cache/data /opt/patriotmetric/storage/framework/sessions /opt/patriotmetric/storage/framework/testing /opt/patriotmetric/storage/framework/views /opt/patriotmetric/storage/logs
chown -R www-data:www-data /opt/patriotmetric/storage /opt/patriotmetric/bootstrap/cache
chmod -R 775 /opt/patriotmetric/storage /opt/patriotmetric/bootstrap/cache

# Create storage link if it doesn't exist
if [ ! -L /opt/patriotmetric/public/storage ]; then
    echo "Creating public storage link..."
    php artisan storage:link 2>/dev/null || true
fi

# Run optimizations depending on APP_ENV
if [ "$APP_ENV" = "production" ]; then
    echo "Optimizing Laravel configuration and routes for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    echo "Publishing Livewire assets..."
    php artisan livewire:publish --assets --force
else
    echo "Clearing configuration caches for development mode..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
fi

# Execute CMD passed from Docker
exec "$@"
