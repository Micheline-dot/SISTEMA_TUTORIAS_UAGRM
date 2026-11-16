<?php

namespace App\DataAccess\PostgreSQL\Administracion\Repositories;

use App\Business\Administracion\Contracts\AdminCourseRepository;
use App\DataAccess\PostgreSQL\Inscripciones\Models\CursoInscripcion;
use App\DataAccess\PostgreSQL\Users\Repositories\PostgreSqlIntegerId;
use Illuminate\Support\Facades\DB;

final class EloquentAdminCourseRepository implements AdminCourseRepository
{
    public const MOTIVO_CANCELACION_CURSO = 'Curso cancelado por la administración';

    /**
     * Lista las tutorías registradas.
     */
    public function listar(string $search = '', string $status = ''): array
    {
        $inscritosActivos = DB::table('curso_inscripcion as ci')
            ->join('inscripcion as i', 'i.id_inscripcion', '=', 'ci.id_inscripcion')
            ->where('i.estado', true)
            ->groupBy('ci.id_curso')
            ->select('ci.id_curso')
            ->selectRaw('COUNT(*) AS total_inscritos');

        $query = DB::table('curso as c')
            ->join('usuario as t', 't.id_usuario', '=', 'c.id_tutor')
            ->leftJoinSub($inscritosActivos, 'ins', 'ins.id_curso', '=', 'c.id_curso')
            ->select([
                'c.id_curso',
                'c.titulo',
                'c.descripcion',
                'c.modalidad',
                'c.cupos',
                'c.es_gratuito',
                'c.fecha_inicio',
                'c.fecha_finalizacion',
                'c.estado',
                't.id_usuario as id_tutor',
                't.correo as correo_tutor',
            ])
            ->selectRaw("TRIM(t.nombres || ' ' || t.apellidos) AS nombre_tutor")
            ->selectRaw('COALESCE(ins.total_inscritos, 0) AS total_inscritos')
            ->orderByDesc('c.id_curso');

        if ($search !== '') {
            $term = '%'.$search.'%';

            $query->where(function ($builder) use ($term): void {
                $builder->where('c.titulo', 'ilike', $term)
                    ->orWhereRaw("(t.nombres || ' ' || t.apellidos) ILIKE ?", [$term]);
            });
        }

        if ($status !== '') {
            $query->where('c.estado', $status);
        }

        return $query->get()
            ->map(fn ($row): array => (array) $row)
            ->all();
    }

    /**
     * Obtiene los datos generales de una tutoría.
     */
    public function buscarDetalle(string $courseId): ?array
    {
        $tutoria = DB::table('curso as c')
            ->join('usuario as t', 't.id_usuario', '=', 'c.id_tutor')
            ->select([
                'c.id_curso',
                'c.id_tutor',
                'c.titulo',
                'c.descripcion',
                'c.modalidad',
                'c.cupos',
                'c.es_gratuito',
                'c.fecha_inicio',
                'c.fecha_finalizacion',
                'c.estado',
                'c.motivo_cancelacion',
                'c.fecha_cancelacion',
                't.correo as correo_tutor',
            ])
            ->selectRaw("TRIM(t.nombres || ' ' || t.apellidos) AS nombre_tutor")
            ->where('c.id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->first();

        return $tutoria ? (array) $tutoria : null;
    }

    /**
     * Lista todas las inscripciones de una tutoría,
     * incluyendo las canceladas y su estado de participación.
     */
    public function listarInscritos(string $courseId): array
    {
        return DB::table('curso_inscripcion as ci')
            ->join('inscripcion as i', 'i.id_inscripcion', '=', 'ci.id_inscripcion')
            ->join('usuario as e', 'e.id_usuario', '=', 'i.id_usuario')
            ->select([
                'i.id_inscripcion',
                'i.id_usuario as id_estudiante',
                'e.correo as correo_estudiante',
                'i.estado as inscripcion_activa',
                'ci.estado as estado_participacion',
                'i.fecha as fecha_inscripcion',
                'i.motivo_cancelacion',
            ])
            ->selectRaw("TRIM(e.nombres || ' ' || e.apellidos) AS nombre_estudiante")
            ->where('ci.id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->orderBy('i.fecha')
            ->orderBy('i.id_inscripcion')
            ->get()
            ->map(fn ($row): array => [
                'id_inscripcion' => (int) $row->id_inscripcion,
                'id_estudiante' => (string) $row->id_estudiante,
                'nombre_estudiante' => $row->nombre_estudiante,
                'correo_estudiante' => $row->correo_estudiante,
                'estado' => (bool) $row->inscripcion_activa ? 'INSCRITO' : 'CANCELADO',
                'asistencia_verificada' => in_array($row->estado_participacion, CursoInscripcion::VERIFICADOS, true),
                'fecha_inscripcion' => $row->fecha_inscripcion,
                'motivo_cancelacion' => $row->motivo_cancelacion,
            ])
            ->all();
    }

    /**
     * Cancela una tutoría y registra el motivo.
     */
    public function cancelarCurso(string $courseId, string $reason): bool
    {
        $actualizados = DB::table('curso')
            ->where('id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->whereNotIn('estado', ['CANCELADO', 'FINALIZADO'])
            ->update([
                'estado' => 'CANCELADO',
                'motivo_cancelacion' => trim($reason),
                'fecha_cancelacion' => now(),
            ]);

        return $actualizados === 1;
    }

    /**
     * Cancela únicamente las inscripciones activas.
     * No modifica el estado de participación (asistencia).
     */
    public function cancelarInscripcionesActivas(string $courseId): int
    {
        $id = PostgreSqlIntegerId::toInteger($courseId);

        $inscripcionesDelCurso = DB::table('curso_inscripcion')
            ->select('id_inscripcion')
            ->where('id_curso', $id);

        $canceladas = DB::table('inscripcion')
            ->whereIn('id_inscripcion', $inscripcionesDelCurso)
            ->where('estado', true)
            ->update([
                'estado' => false,
                'motivo_cancelacion' => self::MOTIVO_CANCELACION_CURSO,
            ]);

        // Las que ya tenían asistencia registrada conservan ese estado (historial).
        DB::table('curso_inscripcion')
            ->where('id_curso', $id)
            ->where('estado', CursoInscripcion::PENDIENTE)
            ->update(['estado' => CursoInscripcion::CANCELADA]);

        return $canceladas;
    }
}
