# Plan de desarrollo — Sistema de Tutorías UAGRM

Basado en el documento del Taller de Grado (cap. 4 RF01–RF23, cap. 5 CU01–CU23,
cap. 7 diagramas de secuencia y script de BD). Arquitectura: Laravel + Livewire en
3 capas (Presentación → Lógica de negocio → Acceso a datos), PostgreSQL 17.

Leyenda: ✅ hecho · 🟡 parcial · ⬜ pendiente

## 1. Roles y lo que puede hacer cada uno

| Rol | Casos de uso |
|---|---|
| **Estudiante** (todo usuario registrado) | CU01, CU02, CU03, CU11, CU12, CU13, CU14, CU16, CU17, CU19 |
| **Tutor** (estudiante con solicitud aprobada) | CU06, CU07, CU08, CU09, CU10, CU15, CU18, CU19, CU23 |
| **Administrador** (independiente) | CU02, CU04, CU05, CU19, CU20, CU21, CU22 |

Un usuario puede ser estudiante y tutor a la vez. El sistema **no procesa pagos**
(4.2.4): solo registra si el curso es gratuito o de pago.

## 2. Estado actual por caso de uso

| CU | RF | Nombre | Prioridad | Estado | Falta |
|---|---|---|---|---|---|
| CU01 | RF01 | Registrar y autenticar usuario | Alta | ✅ | — (registro mínimo: nombres, apellidos, correo @gmail.com, contraseña + confirmación) |
| CU02 | RF02 | Gestionar perfil de usuario | Media | ✅ | — (foto, nombres, apellidos y teléfono editables; correo fijo; facultad + tipo de registro una sola vez: Estudiante → registro · Auxiliar → registro + registro auxiliar · Docente → código docente) |
| CU03 | RF03 | Solicitar habilitación como tutor | Alta | ✅ | — (exige datos académicos de Mi perfil; una sola solicitud pendiente, con índice único parcial; bloqueada si ya fue aprobada; el estudiante ve el estado, el motivo del rechazo y el historial) |
| CU04 | RF04 | Revisar solicitud | Alta | ✅ | — |
| CU05 | RF05 | Validar solicitud | Alta | ✅ | — (notifica aprobación/rechazo con motivo; admins reciben aviso de solicitud nueva) |
| CU06 | RF06 | Gestionar perfil académico | Alta | ✅ | — (biografía, enlaces http/https, conocimientos como etiquetas) |
| CU07 | RF07 | Gestionar cursos | Alta | ✅ | — (borrador con categoría y fechas, eliminar borrador, exige perfil académico completo) |
| CU08 | RF08 | Gestionar temarios | Alta | ✅ | — (temas con fecha dentro del periodo y estado PENDIENTE/COMPLETADO; recursos como enlaces http/https) |
| CU09 | RF09 | Publicar curso | Alta | ✅ | — (valida datos completos, inicio no pasado y al menos un tema; confirmación previa) |
| CU10 | RF10 | Gestionar curso publicado | Alta | ✅ | — (cupos ≥ inscritos, fechas con aviso a inscritos, cancelar con motivo, finalizar) |
| CU11 | RF11 | Realizar búsqueda | Alta | ✅ | — (título, descripción, nombre del tutor y categoría; búsqueda en la URL) |
| CU12 | RF12 | Filtrar resultados | Alta | ✅ | — (categoría, modalidad, costo, valoración mínima del tutor, solo con cupos; ordenar por recientes/próximas/valoración) |
| CU13 | RF13 | Visualizar detalles del curso | Alta | ✅ | — (datos, cupos libres, temario, perfil y valoración del tutor; enlaces de recursos solo para inscritos) |
| CU14 | RF14 | Gestionar inscripción | Alta | ✅ | — (cancelar con motivo y aviso al tutor; confirmar participación tras la asistencia; activas/historial) |
| CU15 | RF15 | Gestionar estudiantes inscritos | Alta | ✅ | — (registrar asistencia, retirar con motivo a quien no tiene asistencia, historial; el retirado no puede reinscribirse) |
| CU16 | RF16 | Calificar tutor y reseña | Media | ✅ | — (1–5 estrellas + reseña, solo con participación confirmada, una vez; aviso al tutor; visible en búsqueda y detalle) |
| CU17 | RF17 | Responder formulario | Media | ✅ | — (encuesta de 5 preguntas tras calificar; JSON en formulario.preguntas; una por calificación) |
| CU18 | RF18 | Ver reseñas y formularios | Media | ✅ | — (promedio, distribución, reseñas, resultados de encuesta y sugerencias; filtro por tutoría) |
| CU19 | RF19 | Recibir notificaciones | Media | ✅ | — (campana con contador en todos los roles, bandeja, marcar una/todas) |
| CU20 | RF20 | Gestionar usuarios | Alta | ✅ | — (buscar/filtrar, crear estudiante/admin, editar, activar/desactivar, quitar rol tutor cancelando sus tutorías publicadas) |
| CU21 | RF21 | Gestionar categorías | Media | ✅ | — (crear, renombrar, eliminar solo si no la usa ningún curso; 14 iniciales por seeder) |
| CU22 | RF22 | Reportes del administrador | Media | ⬜ | Totales, mejores tutores, categoría/facultad más solicitada |
| CU23 | RF23 | Reportes del tutor | Media | ⬜ | Mejor curso, promedio inscritos, publicados/concluidos |

Extra ya implementado: administración general de tutorías (listar, detalle, cancelar).

## 3. Fases (en orden de dependencias)

**Fase 0 — Base**
- 0.1 ✅ Alinear las migraciones con el script del cap. 7.3.2.3 del documento.
- 0.2 ✅ Middleware de rol por ruta + menú de navegación según rol.

**Fase 1 — Usuarios y notificaciones**
- 1.1 ✅ CU01 ajustes de registro.
- 1.2 ✅ CU02 Mi perfil (migración 100018: usuario.foto_perfil, registro_auxiliar,
  codigo_docente; estudiante.registro opcional — extensión al script del PDF).
- 1.3 ✅ CU19 infraestructura de notificaciones (servicio EnviarNotificacion).
- 1.4 ✅ CU20 Gestión de usuarios (admin) (migración 100020: tutor.habilitado).

**Fase 2 — Habilitación y perfil académico**
- 2.1 ✅ CU03 control de solicitud pendiente + estado visible + exige datos académicos
  (migración 100019: índice único parcial de solicitud pendiente).
- 2.2 ✅ CU05 notificar resultado.
- 2.3 ✅ CU06 Perfil académico del tutor.

**Fase 3 — Cursos**
- 3.1 ✅ CU21 Categorías (requisito de CU07) — CategorySeeder.
- 3.2 ✅ CU07 completar gestión de cursos.
- 3.3 ✅ CU08 Temarios y recursos (migración 100021: recurso.enlace, temario.estado).
- 3.4 ✅ CU09 / CU10 publicar y gestionar curso publicado.

**Fase 4 — Búsqueda**: 4.1 ✅ CU11 · 4.2 ✅ CU12 · 4.3 ✅ CU13 (+ regla: el tutor no se inscribe en su propia tutoría).

**Fase 5 — Inscripciones**: 5.1 ✅ CU14 ajustes · 5.2 ✅ CU15 retirar estudiante.

**Fase 6 — Calificaciones**: 6.1 ✅ CU16 · 6.2 ✅ CU17 · 6.3 ✅ CU18 (migración 100022: un formulario por calificación).

**Fase 7 — Reportes**: 7.1 CU22 · 7.2 CU23.

**Fase 8 — Cierre**: pruebas funcionales/integración, despliegue en VPS (Nginx + PHP-FPM).

## 4. Forma de trabajo

Cada paso: explicar qué se modifica → implementar en las 3 capas → pruebas
(automáticas + flujo contra PostgreSQL) → marcar ✅ aquí → commit.
