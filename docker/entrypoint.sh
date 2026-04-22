#!/bin/sh
set -e

# Fix storage symlink (force recreate to ensure it points to container path, not host path)
echo "Fixing storage symlink..."
rm -f /var/www/public/storage
php artisan storage:link

# Ensure correct permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# If a command is passed, execute that instead of php-fpm
if [ $# -gt 0 ]; then
    echo "Executing command: $@"
    exec "$@"
fi

# Start PHP-FPM
echo "Starting PHP-FPM..."
# Migrations will be handled manually to prevent crash loops
exec php-fpm
