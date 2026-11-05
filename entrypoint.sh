#!/bin/bash
set -e

# Siguraduhing may tamang permissions ang storage at cache directories
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# I-clear ang config/cache para siguradong mabasa ang Render environment variables
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Gumawa ng storage symlink kung wala pa
php artisan storage:link --no-interaction || true

# Patakbuhin ang database migrations
echo "Running database migrations..."
php artisan migrate --force

echo "Starting web server..."
if [ $# -gt 0 ]; then
    exec "$@"
else
    exec apache2-foreground
fi
