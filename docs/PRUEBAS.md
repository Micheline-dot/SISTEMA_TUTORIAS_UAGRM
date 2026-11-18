# Pruebas — Sistema de Tutorías UAGRM

## Suites

| Suite | Comando | Base de datos | Qué cubre |
|---|---|---|---|
| Unitarias + Feature | `composer test` (o `php vendor/bin/phpunit`) | SQLite en memoria | Reglas de negocio con dobles de prueba, codificación de archivos (sin BOM), redirección de invitados |
| Integración (funcionales) | `composer test:integration` | PostgreSQL `sistema_tutorias_uagrm_test` | Flujos completos de los CU01–CU23 a través de las 3 capas, pantallas Livewire y control de acceso |

Las pruebas de integración **nunca** usan la base operativa: `tests/Integration/TestCase.php`
verifica el nombre de la base antes de que `RefreshDatabase` ejecute `migrate:fresh`.

Preparación única (la contraseña de PostgreSQL se toma del `.env` local):

```sql
CREATE DATABASE sistema_tutorias_uagrm_test;
```

## Pruebas de integración por caso de uso

| Archivo | Casos de uso | Escenarios |
|---|---|---|
| `Flows/RegistroYPerfilFlowTest` | CU01, CU02 | Solo @gmail.com, contraseña mínima, correo duplicado, inicio de sesión, datos académicos una sola vez, código docente único, datos personales editables y correo fijo, pantalla Mi perfil |
| `Flows/HabilitacionTutorFlowTest` | CU03, CU04, CU05, CU20 | Datos académicos obligatorios, una solicitud pendiente a la vez, aviso al admin, rechazo con observación y nueva solicitud, aprobación → rol tutor, retiro del rol |
| `Flows/TutoriasFlowTest` | CU06–CU10, CU19, CU21 | Perfil académico obligatorio, requisitos para publicar, catálogo, cupos ≥ inscritos, cancelar con aviso, finalizar solo iniciadas, tutorías ajenas (404), cancelación del admin con motivo, categorías sin duplicados ni borrado en uso, notificaciones leídas, crear borrador desde la pantalla |
| `Flows/InscripcionesFlowTest` | CU11–CU15 | Búsqueda por texto/tutor/categoría, cupo lleno, inscripción duplicada, tutor en su propia tutoría, cancelar con motivo y liberar cupo, asistencia + confirmación, retiro sin reinscripción, pantallas |
| `Flows/CalificacionesFlowTest` | CU16–CU18 | Solo con participación confirmada, una calificación, validaciones, encuesta, promedio/distribución/% recomienda, pantalla Calificar |
| `Flows/ReportesFlowTest` | CU22, CU23 | Totales, mejor tutor, categoría y facultad más solicitadas, mejor tutoría, promedio de inscritos, ocupación, periodo, exportación CSV |
| `Flows/PortadaPerfilContactoFlowTest` | CU07, CU13, CU14 (+) | Subir/cambiar/quitar portada, formatos y dueño, visibilidad por estado, crear con portada, perfil público del tutor, teléfono solo para inscritos, enlace de WhatsApp |
| `Flows/LogoSistemaFlowTest` | (+) | Texto por defecto, subir/reemplazar/quitar logo, solo admin, sin SVG, login/registro/menú |
| `Flows/AccesoPorRolTest` | Todos | Matriz de rutas por rol (invitado, estudiante, tutor, administrador), cuenta desactivada, menú por rol |

## Resultado de la última ejecución (17/11/2026)

- Unitarias + Feature: **25/25** ✅ (los 61 de integración aparecen como *skipped* en esta suite, es lo esperado).
- Integración: **61/61** ✅.

## Pruebas manuales recomendadas antes de la defensa

1. Navegador real: registrar cuenta, completar perfil, subir foto.
2. Solicitud de tutor con los 4 PDF de estudiante; abrir los documentos como admin.
3. Publicar tutoría con temario y recursos; inscribirse desde otra cuenta.
4. Asistencia → confirmación → calificación y encuesta → ver reseñas como tutor.
5. Reportes: filtrar por periodo, exportar CSV (abrir en Excel) e imprimir.
6. Vista móvil (ancho < 600 px) del menú y de las tablas.
