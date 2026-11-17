#!/usr/bin/env bash
# Respaldo diario de PostgreSQL + documentos privados — Sistema de Tutorías UAGRM
# Cron sugerido (crontab -e del usuario de despliegue):
#   30 2 * * * /var/www/tutorias-uagrm/deploy/backup-db.sh >> /var/log/tutorias-backup.log 2>&1
# La contraseña de la BD se toma de ~/.pgpass (chmod 600), nunca de este script.
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DEST="${BACKUP_DIR:-/var/backups/tutorias-uagrm}"
DAYS="${BACKUP_DAYS:-14}"
STAMP="$(date +%Y-%m-%d_%H%M)"
DB_NAME="${DB_NAME:-sistema_tutorias_uagrm}"
DB_USER="${DB_USER:-tutorias_app}"

mkdir -p "$DEST"
pg_dump -h 127.0.0.1 -U "$DB_USER" -Fc "$DB_NAME" > "$DEST/bd_$STAMP.dump"
tar -czf "$DEST/archivos_$STAMP.tar.gz" -C "$APP_DIR/storage/app" private

find "$DEST" -type f -mtime +"$DAYS" -delete
echo "$(date '+%F %T') respaldo OK → $DEST (bd_$STAMP.dump, archivos_$STAMP.tar.gz)"
