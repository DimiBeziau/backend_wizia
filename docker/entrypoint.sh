#!/bin/bash
set -e

# Ensure permissions as the files might be mounted from host
# chown -R sammy:sammy /var/www/storage /var/www/bootstrap/cache

# Install dependencies if vendor is missing (unexpected in prod image but good for dev)
if [ ! -d "/var/www/vendor" ]; then
    echo "Vendor directory not found, installing dependencies..."
    composer install --no-interaction --no-dev --optimize-autoloader
fi

# Sync public assets if needed (as per original script)
if [ -d "/var/www/public_mounted" ]; then
    echo "Syncing public assets..."
    cp -R /var/www/public/. /var/www/public_mounted/
fi

# Wait for database to be ready
echo "Waiting for database connection..."
MAX_RETRIES=30
COUNT=0
until php artisan db:monitor --databases=mysql > /dev/null 2>&1 || [ $COUNT -eq $MAX_RETRIES ]; do
    echo "Database not ready, waiting... ($COUNT/$MAX_RETRIES)"
    sleep 2
    COUNT=$((COUNT + 1))
done

if [ $COUNT -eq $MAX_RETRIES ]; then
    echo "Database connection timed out!"
    exit 1
fi

echo "Database is ready!"

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Create storage link if not exists
php artisan storage:link || true

# Clear and cache config for production
echo "Optimizing application..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Application is ready!"

# Execute the main command
exec "$@"
