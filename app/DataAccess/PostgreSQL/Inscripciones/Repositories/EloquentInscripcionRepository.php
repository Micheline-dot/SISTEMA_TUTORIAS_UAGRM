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
    public const MOTIVO_CANCELACION_VOLUNTARIA = 'Cancelada por el estudiante';

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

    public function cancelar(string $studentId, string $courseId): bool
    {
        $idInscripcion = Inscripcion::query()
            ->where('id_usuario', PostgreSqlIntegerId::toInteger($studentId))
            ->where('estado', true)
            ->whereIn('id_inscripcion', $this->inscripcionesDelCurso($courseId))
            ->value('id_inscripcion');

        if ($idInscripcion === null) {
            return false;
        }

        Inscripcion::query()->whereKey($idInscripcion)->update([
            'estado' => false,
            'motivo_cancelacion' => self::MOTIVO_CANCELACION_VOLUNTARIA,
        ]);

        CursoInscripcion::query()
            ->where('id_inscripcion', $idInscripcion)
            ->where('id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->update(['estado' => CursoInscripcion::CANCELADA]);

        return true;
    }

    public function listarPorEstudiante(string $studentId): array
    {
        return $this->baseQuery()
            ->join('curso as c', 'c.id_curso', '=', 'ci.id_curso')
            ->join('usuario as t', 't.id_usuario', '=', 'c.id_tutor')
            ->where('i.id_usuario', PostgreSqlIntegerId::toInteger($studentId))
            ->addSelect([
                'c.titulo', 'c.descripcion', 'c.modalidad', 'c.estado as estado_curso',
                't.id_usuario as id_tutor', 't.nombres as tutor_nombres', 't.apellidos as tutor_apellidos',
            ])
            ->orderByDesc('i.fecha')
            ->orderByDesc('i.id_inscripcion')
            ->get()
            ->map(fn ($row): array => $this->toArray($row) + [
                'curso' => [
                    'id_curso' => (int) $row->id_curso,
                    'titulo' => $row->titulo,
                    'descripcion' => $row->descripcion,
                    'modalidad' => $row->modalidad,
                    'estado' => $row->estado_curso,
                    'tutor' => [
                        'id_usuario' => (int) $row->id_tutor,
                        'nombre_completo' => trim($row->tutor_nombres.' '.$row->tutor_apellidos),
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

    private function inscripcionesDelCurso(string $courseId): Builder
    {
        return DB::table('curso_inscripcion')
            ->select('id_inscripcion')
            ->where('id_curso', PostgreSqlIntegerId::toInteger($courseId));
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
