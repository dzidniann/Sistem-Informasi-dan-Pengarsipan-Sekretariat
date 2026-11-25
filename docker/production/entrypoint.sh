#!/bin/sh
set -eu
umask 027
mkdir -p storage/app/protected-uploads storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
# Never migrate, seed, or generate a key implicitly on container startup.
php artisan config:cache
exec "$@"
