#!/bin/sh
set -e

# Create the storage symlink if it doesn't exist
if [ ! -L /var/www/public/storage ]; then
    echo "Creating storage symlink..."
    php artisan storage:link
fi

# Run database migrations
php artisan migrate --force

# Start PHP-FPM
exec php-fpm
