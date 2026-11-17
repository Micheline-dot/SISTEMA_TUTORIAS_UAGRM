#!/usr/bin/env bash
# Despliegue / actualización en el VPS — Sistema de Tutorías UAGRM
# Uso (en el servidor, como el usuario dueño del código):
#   cd /var/www/tutorias-uagrm && bash deploy/deploy.sh [rama]
# Requiere que la instalación inicial de docs/DESPLIEGUE.md ya esté hecha (.env, APP_KEY, BD).
set -euo pipefail

BRANCH="${1:-main}"
APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.4-fpm}"
WEB_USER="${WEB_USER:-www-data}"

cd "$APP_DIR"

if [[ ! -f .env ]]; then
    echo "Falta .env (ver deploy/env.production.example)." >&2
    exit 1
fi

if grep -Eq '^APP_DEBUG=true' .env; then
    echo "APP_DEBUG=true en producción: corrígelo antes de desplegar." >&2
    exit 1
fi

echo "==> Modo mantenimiento"
php artisan down --retry=30 || true
trap 'php artisan up' EXIT

echo "==> Código ($BRANCH)"
git fetch --prune origin
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

echo "==> Dependencias PHP (sin dev)"
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

echo "==> Migraciones (solo aditivas)"
php artisan migrate --force

echo "==> Catálogo de categorías (idempotente)"
php artisan db:seed --class=Database\\Seeders\\CategorySeeder --force

echo "==> Cachés de producción"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Permisos de escritura"
sudo chown -R "$USER":"$WEB_USER" storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 2775 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;

echo "==> Recargar PHP-FPM (limpia OPcache)"
sudo systemctl reload "$PHP_FPM_SERVICE"

echo "Despliegue terminado."
