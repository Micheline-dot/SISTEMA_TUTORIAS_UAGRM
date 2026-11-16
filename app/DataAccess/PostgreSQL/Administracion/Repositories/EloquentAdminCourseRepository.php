<?php

namespace App\DataAccess\PostgreSQL\Administracion\Repositories;

use App\Business\Administracion\Contracts\AdminCourseRepository;
use App\DataAccess\PostgreSQL\Users\Repositories\PostgreSqlIntegerId;
use Illuminate\Support\Facades\DB;

final class EloquentAdminCourseRepository implements AdminCourseRepository
{
    /**
     * Lista las tutorías registradas.
     */
    public function listar(
        string $search = '',
        string $status = ''
    ): array {
        $query = DB::table('cursos as c')
            ->join('usuarios as t', 't.id_usuario', '=', 'c.id_tutor')
            ->leftJoin('inscripciones as i', function ($join): void {
                $join->on('i.id_curso', '=', 'c.id_curso')
                    ->where('i.estado', '=', 'INSCRITO');
            })
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
                't.nombre_completo as nombre_tutor',
                't.correo as correo_tutor',
            ])
            ->selectRaw('COUNT(i.id_inscripcion) AS total_inscritos')
            ->groupBy([
                'c.id_curso',
                'c.titulo',
                'c.descripcion',
                'c.modalidad',
                'c.cupos',
                'c.es_gratuito',
                'c.fecha_inicio',
                'c.fecha_finalizacion',
                'c.estado',
                't.id_usuario',
                't.nombre_completo',
                't.correo',
            ])
            ->orderByDesc('c.id_curso');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('c.titulo', 'ilike', '%' . $search . '%')
                    ->orWhere(
                        't.nombre_completo',
                        'ilike',
                        '%' . $search . '%'
                    );
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
        $id = PostgreSqlIntegerId::toInteger($courseId);

        $tutoria = DB::table('cursos as c')
            ->join('usuarios as t', 't.id_usuario', '=', 'c.id_tutor')
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
                't.nombre_completo as nombre_tutor',
                't.correo as correo_tutor',
            ])
            ->where('c.id_curso', $id)
            ->first();

        return $tutoria ? (array) $tutoria : null;
    }

    /**
     * Lista todas las inscripciones de una tutoría,
     * incluyendo las canceladas y su historial de asistencia.
     */
    public function listarInscritos(string $courseId): array
    {
        $id = PostgreSqlIntegerId::toInteger($courseId);

        return DB::table('inscripciones as i')
            ->join(
                'usuarios as e',
                'e.id_usuario',
                '=',
                'i.id_estudiante'
            )
            ->select([
                'i.id_inscripcion',
                'i.id_estudiante',
                'e.nombre_completo as nombre_estudiante',
                'e.correo as correo_estudiante',
                'i.estado',
                'i.asistencia_verificada',
                'i.fecha_inscripcion',
                'i.fecha_cancelacion',
            ])
            ->where('i.id_curso', $id)
            ->orderBy('i.fecha_inscripcion')
            ->get()
            ->map(fn ($row): array => (array) $row)
            ->all();
    }

    /**
     * Cancela una tutoría y registra el motivo.
     */
    public function cancelarCurso(
        string $courseId,
        string $reason
    ): bool {
        $id = PostgreSqlIntegerId::toInteger($courseId);

        $actualizados = DB::table('cursos')
            ->where('id_curso', $id)
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
     * No modifica la asistencia verificada.
     */
    public function cancelarInscripcionesActivas(
        string $courseId
    ): int {
        $id = PostgreSqlIntegerId::toInteger($courseId);

        return DB::table('inscripciones')
            ->where('id_curso', $id)
            ->where('estado', 'INSCRITO')
            ->update([
                'estado' => 'CANCELADO',
                'fecha_cancelacion' => now(),
            ]);
    }
}