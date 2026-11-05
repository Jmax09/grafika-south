#!/bin/sh

# Caching runs here, at container start, and NOT in the Dockerfile. A
# build-time config:cache would bake in the config as it looked during the
# build — with no .env in the image and no Render env vars yet, every DB_*
# value would freeze at its MySQL default and the container would connect to
# 127.0.0.1:3306.
#
# None of these may stop Apache from starting, so each is guarded: the worst
# case is an unoptimized request, not a container that never binds the port.
php artisan config:cache || echo "config:cache failed - using uncached config" >&2
php artisan route:cache || echo "route:cache failed - using uncached routes" >&2
php artisan view:cache || echo "view:cache failed - views compile on demand" >&2

# Written as root above; Apache serves as www-data and has to be able to
# rotate these and write sessions/logs.
chown -R www-data:www-data /var/www/html/bootstrap/cache /var/www/html/storage

# Migrations must not gate Apache: a DB outage should surface as a site error,
# not as a container that never binds the port.
attempt=1
until php artisan migrate --force; do
    if [ "$attempt" -ge 3 ]; then
        echo "migrate failed after $attempt attempts - starting web server anyway" >&2
        break
    fi
    attempt=$((attempt + 1))
    sleep 5
done

exec "$@"
