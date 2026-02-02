#!/bin/bash
set -e

# Wait for database to be ready
echo "Waiting for database connection..."
while ! php artisan db:monitor --databases=mysql 2>/dev/null; do
    echo "Database not ready, waiting..."
    sleep 2
done

echo "Database is ready!"

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Clear and cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Application is ready!"

# Execute the main command
exec "$@"
