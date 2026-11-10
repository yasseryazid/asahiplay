#!/bin/sh
# Entrypoint produksi: tidak mengubah .env, hanya menyiapkan aplikasi.
set -e
cd /var/www/html

[ -f .env ] || { echo "!! .env belum ada. Salin .env.production.example ke .env lalu isi."; exit 1; }

composer install --no-dev --optimize-autoloader --no-interaction
grep -q '^APP_KEY=base64' .env || php artisan key:generate --force

until php -r 'new PDO("mysql:host=".getenv("DB_HOST").";dbname=".getenv("DB_DATABASE"),getenv("DB_USERNAME"),getenv("DB_PASSWORD"));' 2>/dev/null; do echo ">> Menunggu database..."; sleep 3; done

php artisan migrate --force
php artisan db:seed --class=QuestionBankSeeder --force
php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

echo ">> Siap (produksi)."
exec "$@"
