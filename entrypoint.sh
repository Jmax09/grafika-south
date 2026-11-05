#!/bin/sh

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
