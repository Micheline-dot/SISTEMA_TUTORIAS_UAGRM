# Despliegue en VPS — Sistema de Tutorías UAGRM

Guía para instalar el sistema en un VPS Linux con **Nginx + PHP-FPM + PostgreSQL 17**.
Probada como referencia para Ubuntu Server 24.04 LTS. Los archivos de configuración
están en [`deploy/`](../deploy).

| Archivo | Para qué sirve |
|---|---|
| `deploy/nginx/tutorias-uagrm.conf` | Sitio de Nginx (raíz `public/`, límite de subida 25 MB, cabeceras de seguridad) |
| `deploy/php/tutorias-uagrm.ini` | Ajustes de PHP-FPM (subidas, zona horaria, OPcache) |
| `deploy/env.production.example` | Plantilla del `.env` de producción (sin secretos) |
| `deploy/deploy.sh` | Actualizar el sistema: mantenimiento, `git pull`, dependencias, migraciones, cachés, permisos |
| `deploy/backup-db.sh` | Respaldo diario de la BD y de los documentos privados |

> **Secretos:** las contraseñas (BD, administrador inicial) y `APP_KEY` se escriben
> directamente en el servidor. No se suben al repositorio ni se comparten por chat.

---

## 1. Requisitos del servidor

- 1 vCPU, 2 GB RAM, 20 GB de disco como mínimo.
- Dominio apuntando a la IP del VPS (opcional, pero necesario para HTTPS).
- Puertos 22, 80 y 443 abiertos.

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx git unzip curl ufw

# PHP 8.4 (repositorio ondrej/php) y extensiones que usa el proyecto
sudo add-apt-repository -y ppa:ondrej/php
sudo apt install -y php8.4-fpm php8.4-cli php8.4-pgsql php8.4-mbstring php8.4-xml \
    php8.4-curl php8.4-zip php8.4-bcmath php8.4-intl php8.4-gd php8.4-opcache

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# PostgreSQL 17 (repositorio oficial PGDG)
sudo install -d /usr/share/postgresql-common/pgdg
sudo curl -o /usr/share/postgresql-common/pgdg/apt.postgresql.org.asc --fail https://www.postgresql.org/media/keys/ACCC4CF8.asc
echo "deb [signed-by=/usr/share/postgresql-common/pgdg/apt.postgresql.org.asc] https://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" | sudo tee /etc/apt/sources.list.d/pgdg.list
sudo apt update && sudo apt install -y postgresql-17

# Cortafuegos
sudo ufw allow OpenSSH && sudo ufw allow 'Nginx Full' && sudo ufw enable
```

## 2. Base de datos

Crear un usuario propio de la aplicación (no usar `postgres`). `\password` pide la
contraseña de forma oculta en la terminal:

```bash
sudo -u postgres psql
```
```sql
CREATE USER tutorias_app;
\password tutorias_app
CREATE DATABASE sistema_tutorias_uagrm OWNER tutorias_app ENCODING 'UTF8' TEMPLATE template0;
\q
```

PostgreSQL escucha solo en `localhost` por defecto; no es necesario exponer el puerto 5432.

## 3. Código y configuración

```bash
sudo mkdir -p /var/www/tutorias-uagrm
sudo chown "$USER":www-data /var/www/tutorias-uagrm
git clone <URL-del-repositorio> /var/www/tutorias-uagrm
cd /var/www/tutorias-uagrm
git checkout main            # o la rama que se publique

composer install --no-dev --prefer-dist --optimize-autoloader
cp deploy/env.production.example .env
chmod 640 .env
nano .env                    # completar APP_URL, DB_PASSWORD y los ADMIN_*
php artisan key:generate --force
```

## 4. Esquema, catálogos y administrador inicial

Las migraciones crean el esquema del Taller de Grado (cap. 7.3.2.3) más las columnas
marcadas con **(+)** en `database/DISENO_BASE_DE_DATOS.md`.

```bash
php artisan migrate --force
php artisan db:seed --force    # administrador inicial (ADMIN_*) + 14 categorías
```

Después de ver el mensaje *"Administrador inicial creado"*, **borrar `ADMIN_PASSWORD`
del `.env`**. El seeder no vuelve a crear administradores si ya existe uno.

## 5. Permisos, PHP-FPM y Nginx

```bash
sudo chown -R "$USER":www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 2775 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;

sudo cp deploy/php/tutorias-uagrm.ini /etc/php/8.4/fpm/conf.d/99-tutorias-uagrm.ini
sudo systemctl reload php8.4-fpm

sudo cp deploy/nginx/tutorias-uagrm.conf /etc/nginx/sites-available/tutorias-uagrm
sudo sed -i 's/tutorias.example.com/TU-DOMINIO/' /etc/nginx/sites-available/tutorias-uagrm
sudo ln -s /etc/nginx/sites-available/tutorias-uagrm /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx

php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Los documentos de las solicitudes y las fotos de perfil se guardan en
`storage/app/private` y solo se sirven a través de rutas autenticadas
(no se necesita `storage:link`).

## 6. HTTPS

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d TU-DOMINIO
```

Con HTTPS activo, mantener `SESSION_SECURE_COOKIE=true` y `APP_URL=https://…` en el `.env`.
Sin dominio (solo IP), poner temporalmente `SESSION_SECURE_COOKIE=false`.

## 7. Actualizaciones

```bash
cd /var/www/tutorias-uagrm
bash deploy/deploy.sh main
```

El script pone el sitio en mantenimiento, actualiza el código, instala dependencias,
ejecuta las migraciones (todas son aditivas), regenera cachés, corrige permisos y
recarga PHP-FPM. Si algo falla, el sitio vuelve a quedar en línea.

## 8. Respaldos

```bash
# ~/.pgpass  (chmod 600)  →  127.0.0.1:5432:sistema_tutorias_uagrm:tutorias_app:<contraseña>
sudo mkdir -p /var/backups/tutorias-uagrm && sudo chown "$USER" /var/backups/tutorias-uagrm
chmod +x deploy/backup-db.sh
crontab -e   # 30 2 * * * /var/www/tutorias-uagrm/deploy/backup-db.sh >> /var/log/tutorias-backup.log 2>&1
```

Restaurar: `pg_restore -h 127.0.0.1 -U tutorias_app -d sistema_tutorias_uagrm --clean bd_FECHA.dump`
y descomprimir `archivos_FECHA.tar.gz` en `storage/app/`.

## 9. Verificación después de desplegar

1. `https://TU-DOMINIO/` redirige a `/login`.
2. Iniciar sesión con el administrador inicial → menú **Administración** visible.
3. Registrar una cuenta `@gmail.com` → completar datos académicos en **Mi perfil**.
4. Solicitar ser tutor → aprobar como administrador → la cuenta ve **Área de tutor**.
5. Crear y publicar una tutoría → aparece en **Buscar tutorías** para otra cuenta.
6. `tail -f storage/logs/laravel-*.log` sin errores.

## 10. Problemas frecuentes

| Síntoma | Causa / solución |
|---|---|
| Error 500 sin detalle | Revisar `storage/logs/`; permisos de `storage` y `bootstrap/cache` |
| "419 Page expired" | `APP_URL` o `SESSION_SECURE_COOKIE` no coinciden con http/https |
| Subida de PDF falla | `client_max_body_size` (Nginx) y `upload_max_filesize` (PHP) |
| Cambios de código no se ven | `php artisan optimize:clear` y `sudo systemctl reload php8.4-fpm` (OPcache) |
| Botones de Livewire sin respuesta | Archivo con BOM UTF-8; `php vendor/bin/phpunit` lo detecta (SourceEncodingTest) |
