<?php

namespace App\DataAccess\PostgreSQL\Inscripciones\Repositories;

use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\DataAccess\PostgreSQL\Courses\Models\Course;
use App\DataAccess\PostgreSQL\Inscripciones\Models\CursoInscripcion;
use App\DataAccess\PostgreSQL\Inscripciones\Models\Inscripcion;
use App\DataAccess\PostgreSQL\Users\Repositories\PostgreSqlIntegerId;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Traduce las tablas inscripcion + curso_inscripcion a la estructura que
 * usa la lógica de negocio:
 *   estado               'INSCRITO' | 'CANCELADO'   (inscripcion.estado true/false)
 *   asistencia_verificada bool                      (curso_inscripcion.estado A o C)
 *   fecha_inscripcion    inscripcion.fecha
 */
final class EloquentInscripcionRepository implements InscripcionRepository
{
    public function bloquearCurso(string $courseId): ?array
    {
        $course = Course::query()
            ->where('id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->lockForUpdate()
            ->first();

        return $course?->toArray();
    }

    public function buscarInscripcionActiva(string $studentId, string $courseId): ?array
    {
        $row = $this->baseQuery()
            ->where('i.id_usuario', PostgreSqlIntegerId::toInteger($studentId))
            ->where('ci.id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->where('i.estado', true)
            ->first();

        return $row === null ? null : $this->toArray($row);
    }

    public function buscarInscripcionAnterior(string $studentId, string $courseId): ?array
    {
        $row = $this->baseQuery()
            ->where('i.id_usuario', PostgreSqlIntegerId::toInteger($studentId))
            ->where('ci.id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->whereIn('ci.estado', CursoInscripcion::VERIFICADOS)
            ->first();

        return $row === null ? null : $this->toArray($row);
    }

    public function contarInscripcionesActivas(string $courseId): int
    {
        return $this->baseQuery()
            ->where('ci.id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->where('i.estado', true)
            ->count();
    }

    public function crear(string $studentId, string $courseId): int
    {
        $inscripcion = Inscripcion::query()->create([
            'id_usuario' => PostgreSqlIntegerId::toInteger($studentId),
            'estado' => true,
        ]);

        CursoInscripcion::query()->create([
            'id_curso' => PostgreSqlIntegerId::toInteger($courseId),
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'estado' => CursoInscripcion::PENDIENTE,
        ]);

        return (int) $inscripcion->id_inscripcion;
    }

    public function cancelar(string $studentId, string $courseId, string $motivo): bool
    {
        return $this->desactivar($studentId, $courseId, $motivo, CursoInscripcion::CANCELADA, false);
    }

    public function retirar(string $studentId, string $courseId, string $motivo): bool
    {
        return $this->desactivar($studentId, $courseId, $motivo, CursoInscripcion::ELIMINADA, true);
    }

    public function fueRetirado(string $studentId, string $courseId): bool
    {
        return $this->baseQuery()
            ->where('i.id_usuario', PostgreSqlIntegerId::toInteger($studentId))
            ->where('ci.id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->where('ci.estado', CursoInscripcion::ELIMINADA)
            ->exists();
    }

    public function confirmarParticipacion(string $studentId, string $courseId): bool
    {
        $inscripcionActiva = Inscripcion::query()
            ->select('id_inscripcion')
            ->where('id_usuario', PostgreSqlIntegerId::toInteger($studentId))
            ->where('estado', true);

        return CursoInscripcion::query()
            ->where('id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->whereIn('id_inscripcion', $inscripcionActiva)
            ->where('estado', CursoInscripcion::ASISTENCIA_REGISTRADA)
            ->update(['estado' => CursoInscripcion::PARTICIPACION_CONFIRMADA]) === 1;
    }

    public function listarInactivasPorCurso(string $courseId): array
    {
        return $this->baseQuery()
            ->join('usuario as u', 'u.id_usuario', '=', 'i.id_usuario')
            ->where('ci.id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->where('i.estado', false)
            ->addSelect(['u.nombres', 'u.apellidos', 'u.correo'])
            ->orderByDesc('i.id_inscripcion')
            ->get()
            ->map(fn ($row): array => $this->toArray($row) + [
                'nombre_completo' => trim($row->nombres.' '.$row->apellidos),
                'correo' => $row->correo,
            ])
            ->all();
    }

    /**
     * Desactiva la inscripción activa del estudiante en el curso, guardando el
     * motivo. Si $soloPendiente, solo procede cuando la participación no fue
     * verificada (estado "activa").
     */
    private function desactivar(string $studentId, string $courseId, string $motivo, string $estado, bool $soloPendiente): bool
    {
        $idCurso = PostgreSqlIntegerId::toInteger($courseId);

        $idInscripcion = Inscripcion::query()
            ->where('id_usuario', PostgreSqlIntegerId::toInteger($studentId))
            ->where('estado', true)
            ->whereIn('id_inscripcion', DB::table('curso_inscripcion')
                ->select('id_inscripcion')
                ->where('id_curso', $idCurso)
                ->when($soloPendiente, fn ($q) => $q->where('estado', CursoInscripcion::PENDIENTE)))
            ->value('id_inscripcion');

        if ($idInscripcion === null) {
            return false;
        }

        Inscripcion::query()->whereKey($idInscripcion)->update([
            'estado' => false,
            'motivo_cancelacion' => mb_substr($motivo, 0, 500),
        ]);

        CursoInscripcion::query()
            ->where('id_inscripcion', $idInscripcion)
            ->where('id_curso', $idCurso)
            ->update(['estado' => $estado]);

        return true;
    }

    public function listarPorEstudiante(string $studentId): array
    {
        return $this->baseQuery()
            ->join('curso as c', 'c.id_curso', '=', 'ci.id_curso')
            ->join('usuario as t', 't.id_usuario', '=', 'c.id_tutor')
            ->leftJoin('calificacion_resena as cr', function ($j): void {
                $j->on('cr.id_inscripcion', '=', 'ci.id_inscripcion')->on('cr.id_curso', '=', 'ci.id_curso');
            })
            ->leftJoin('formulario as f', 'f.id_calificacion', '=', 'cr.id_calificacion')
            ->where('i.id_usuario', PostgreSqlIntegerId::toInteger($studentId))
            ->addSelect([
                'c.titulo', 'c.descripcion', 'c.modalidad', 'c.estado as estado_curso', 'c.fecha_inicio', 'c.fecha_finalizacion',
                't.id_usuario as id_tutor', 't.nombres as tutor_nombres', 't.apellidos as tutor_apellidos', 't.telefono as tutor_telefono',
                'cr.puntuacion as calificacion', 'f.id_formulario',
            ])
            ->orderByDesc('i.fecha')
            ->orderByDesc('i.id_inscripcion')
            ->get()
            ->map(fn ($row): array => $this->toArray($row) + [
                // CU16/CU17: estado de la calificación de esta inscripción.
                'calificacion' => $row->calificacion === null ? null : (int) $row->calificacion,
                'encuesta_respondida' => $row->id_formulario !== null,
                'curso' => [
                    'id_curso' => (int) $row->id_curso,
                    'titulo' => $row->titulo,
                    'descripcion' => $row->descripcion,
                    'modalidad' => $row->modalidad,
                    'estado' => $row->estado_curso,
                    'fecha_inicio' => $row->fecha_inicio,
                    'fecha_finalizacion' => $row->fecha_finalizacion,
                    'tutor' => [
                        'id_usuario' => (int) $row->id_tutor,
                        'nombre_completo' => trim($row->tutor_nombres.' '.$row->tutor_apellidos),
                        'telefono' => $row->tutor_telefono,
                    ],
                ],
            ])
            ->all();
    }

    public function listarActivasPorCurso(string $courseId): array
    {
        return $this->baseQuery()
            ->join('usuario as u', 'u.id_usuario', '=', 'i.id_usuario')
            ->where('ci.id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->where('i.estado', true)
            ->addSelect(['u.nombres', 'u.apellidos', 'u.correo'])
            ->orderBy('u.apellidos')
            ->orderBy('u.nombres')
            ->get()
            ->map(fn ($row): array => $this->toArray($row) + [
                'nombre_completo' => trim($row->nombres.' '.$row->apellidos),
                'correo' => $row->correo,
            ])
            ->all();
    }

    public function verificarAsistencia(string $studentId, string $courseId): bool
    {
        $inscripcionActiva = Inscripcion::query()
            ->select('id_inscripcion')
            ->where('id_usuario', PostgreSqlIntegerId::toInteger($studentId))
            ->where('estado', true);

        return CursoInscripcion::query()
            ->where('id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->whereIn('id_inscripcion', $inscripcionActiva)
            ->where('estado', CursoInscripcion::PENDIENTE)
            ->update(['estado' => CursoInscripcion::ASISTENCIA_REGISTRADA]) === 1;
    }

    /** Ambas tablas tienen "estado": se seleccionan columnas explícitas con alias. */
    private function baseQuery(): Builder
    {
        return DB::table('inscripcion as i')
            ->join('curso_inscripcion as ci', 'ci.id_inscripcion', '=', 'i.id_inscripcion')
            ->select([
                'i.id_inscripcion', 'i.id_usuario', 'i.estado', 'i.fecha', 'i.motivo_cancelacion',
                'ci.id_curso', 'ci.estado as estado_participacion',
            ]);
    }

    private function toArray(object $row): array
    {
        $estadoParticipacion = $row->estado_participacion ?? null;

        return [
            'id_inscripcion' => (int) $row->id_inscripcion,
            'id_curso' => (int) $row->id_curso,
            'id_estudiante' => (string) $row->id_usuario,
            'estado' => (bool) $row->estado ? 'INSCRITO' : 'CANCELADO',
            'estado_participacion' => $estadoParticipacion,
            'asistencia_verificada' => in_array($estadoParticipacion, CursoInscripcion::VERIFICADOS, true),
            'fecha_inscripcion' => $row->fecha,
            'motivo_cancelacion' => $row->motivo_cancelacion ?? null,
        ];
    }
}
