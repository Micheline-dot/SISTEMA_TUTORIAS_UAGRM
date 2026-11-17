# Sistema de Tutorías UAGRM

Plataforma web para gestionar tutorías académicas entre estudiantes, tutores y
administradores de la UAGRM. Proyecto final de **INF512 — Ingeniería de Software**,
basado en el documento del Taller de Grado (RF01–RF23, CU01–CU23).

**Stack:** Laravel 13 · Livewire 4 · PHP 8.4 · PostgreSQL 17

## Funcionalidades

| Rol | Puede |
|---|---|
| Estudiante | Registrarse (@gmail.com), completar su perfil académico, buscar y filtrar tutorías, inscribirse, cancelar con motivo, confirmar su participación, calificar al tutor y responder la encuesta, solicitar ser tutor |
| Tutor | Perfil académico, crear/publicar tutorías con temario y recursos, gestionar cupos y fechas, cancelar o finalizar, registrar asistencia, retirar estudiantes, ver reseñas y encuestas, reportes propios |
| Administrador | Revisar solicitudes de tutor, gestionar usuarios y categorías, cancelar tutorías, reportes generales |

Todos los roles reciben notificaciones internas (CU19).

## Arquitectura (3 capas)

```text
app/Presentation      Livewire, controladores, middleware (sin reglas de negocio)
app/Business          Casos de uso, contratos, DTO y excepciones
app/DataAccess        Repositorios PostgreSQL (Eloquent / Query Builder)
```

Los contratos se enlazan con sus implementaciones en `app/Providers/BusinessServiceProvider.php`.

## Instalación local

```bash
composer install
cp .env.example .env          # completar DB_PASSWORD y ADMIN_* en el propio archivo
php artisan key:generate
php artisan migrate
php artisan db:seed           # administrador inicial + 14 categorías
php artisan serve             # http://localhost:8000
```

## Documentación

- [Plan de desarrollo y estado de los CU](docs/PLAN_DESARROLLO.md)
- [Diseño de la base de datos](database/DISENO_BASE_DE_DATOS.md)
- [Pruebas](docs/PRUEBAS.md) — `composer test` y `composer test:integration`
- [Despliegue en VPS (Nginx + PHP-FPM)](docs/DESPLIEGUE.md)
