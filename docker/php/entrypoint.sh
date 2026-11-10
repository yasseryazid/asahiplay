#!/bin/sh
set -e
cd /var/www/html

[ -f .env ] || { cp .env.example .env; NEWKEY=1; }
[ -f vendor/autoload.php ] || composer install --no-interaction
[ -n "$NEWKEY" ] && php artisan key:generate --force

set_env() { grep -q "^$1=" .env && sed -i "s|^$1=.*|$1=$2|" .env || echo "$1=$2" >> .env; }
set_env APP_NAME Asahi
set_env APP_URL "http://${APP_HOST:-asahi.test}"
set_env DB_CONNECTION mysql
set_env DB_HOST db
set_env DB_PORT 3306
set_env DB_DATABASE asahi
set_env DB_USERNAME asahi
set_env DB_PASSWORD secret
grep -q "^ASAHI_IKSC_PUBLIC=" .env || set_env ASAHI_IKSC_PUBLIC false
grep -q "^GOOGLE_REDIRECT_URI=." .env || set_env GOOGLE_REDIRECT_URI http://localhost:8000/auth/google/callback

until php -r 'new PDO("mysql:host=db;dbname=asahi","asahi","secret");' 2>/dev/null; do echo ">> Menunggu database..."; sleep 2; done

php artisan migrate --force --seed
php artisan storage:link 2>/dev/null || true
php artisan config:clear
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

echo ">> Siap."
exec "$@"
