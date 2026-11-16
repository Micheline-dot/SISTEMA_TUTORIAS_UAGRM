<?php

namespace App\DataAccess\PostgreSQL\Inscripciones\Repositories;

use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\DataAccess\PostgreSQL\Courses\Models\Course;
use App\DataAccess\PostgreSQL\Inscripciones\Models\Inscripcion;
use App\DataAccess\PostgreSQL\Users\Repositories\PostgreSqlIntegerId;

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

    public function buscarInscripcionActiva(
        string $studentId,
        string $courseId
    ): ?array {
        $inscripcion = Inscripcion::query()
            ->where('id_estudiante', PostgreSqlIntegerId::toInteger($studentId))
            ->where('id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->where('estado', 'INSCRITO')
            ->first();

        return $inscripcion?->toArray();
    }

    public function buscarInscripcionAnterior(
        string $studentId,
        string $courseId
    ): ?array {
        $inscripcion = Inscripcion::query()
            ->where('id_estudiante', PostgreSqlIntegerId::toInteger($studentId))
            ->where('id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->where('asistencia_verificada', true)
            ->first();

        return $inscripcion?->toArray();
    }

    public function contarInscripcionesActivas(string $courseId): int
    {
        return Inscripcion::query()
            ->where('id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->where('estado', 'INSCRITO')
            ->count();
    }

    public function crear(string $studentId, string $courseId): int
    {
        $inscripcion = Inscripcion::query()->create([
            'id_estudiante' => PostgreSqlIntegerId::toInteger($studentId),
            'id_curso' => PostgreSqlIntegerId::toInteger($courseId),
            'estado' => 'INSCRITO',
        ]);

        return (int) $inscripcion->id_inscripcion;
    }

    public function cancelar(string $studentId, string $courseId): bool
    {
        return Inscripcion::query()
            ->where('id_estudiante', PostgreSqlIntegerId::toInteger($studentId))
            ->where('id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->where('estado', 'INSCRITO')
            ->update([
                'estado' => 'CANCELADO',
                'fecha_cancelacion' => now(),
            ]) === 1;
    }

    public function listarPorEstudiante(string $studentId): array
    {
        return Inscripcion::query()
            ->with('curso.tutor')
            ->where('id_estudiante', PostgreSqlIntegerId::toInteger($studentId))
            ->orderByDesc('fecha_inscripcion')
            ->get()
            ->toArray();
    }

    public function listarActivasPorCurso(string $courseId): array
    {
        return Inscripcion::query()
            ->join(
                'usuarios as estudiantes',
                'estudiantes.id_usuario',
                '=',
                'inscripciones.id_estudiante'
            )
            ->where('inscripciones.id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->where('inscripciones.estado', 'INSCRITO')
            ->orderBy('estudiantes.nombre_completo')
            ->get([
                'inscripciones.id_inscripcion',
                'inscripciones.id_curso',
                'inscripciones.id_estudiante',
                'inscripciones.estado',
                'inscripciones.asistencia_verificada',
                'inscripciones.fecha_inscripcion',
                'estudiantes.nombre_completo',
                'estudiantes.correo',
            ])
            ->map(fn ($row): array => [
                'id_inscripcion' => (int) $row->id_inscripcion,
                'id_curso' => (int) $row->id_curso,
                'id_estudiante' => (string) $row->id_estudiante,
                'nombre_completo' => $row->nombre_completo,
                'correo' => $row->correo,
                'estado' => $row->estado,
                'asistencia_verificada' => (bool) $row->asistencia_verificada,
                'fecha_inscripcion' => $row->fecha_inscripcion,
            ])
            ->all();
    }

    public function verificarAsistencia(
        string $studentId,
        string $courseId
    ): bool {
        return Inscripcion::query()
            ->where('id_estudiante', PostgreSqlIntegerId::toInteger($studentId))
            ->where('id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->where('estado', 'INSCRITO')
            ->where('asistencia_verificada', false)
            ->update([
                'asistencia_verificada' => true,
            ]) === 1;
    }
}
