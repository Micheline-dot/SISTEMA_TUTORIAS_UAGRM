<?php

namespace App\DataAccess\PostgreSQL\Reportes\Repositories;

use App\Business\Reportes\Contracts\ReportRepository;
use App\Business\Reportes\DTO\ReportPeriod;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class DbReportRepository implements ReportRepository
{
    private const EFECTIVAS = ['activa', 'asistio', 'confirmada'];

    public function adminTotals(ReportPeriod $period): array
    {
        $cursos = DB::table('curso')
            ->selectRaw('estado, COUNT(*) AS total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->map(fn ($v) => (int) $v)
            ->all();

        $ins = $this->within(
            DB::table('curso_inscripcion as ci')->join('inscripcion as i', 'i.id_inscripcion', '=', 'ci.id_inscripcion'),
            'i.fecha',
            $period,
        )->selectRaw(
            "COUNT(*) AS total,
             COUNT(*) FILTER (WHERE ci.estado IN ('activa','asistio','confirmada')) AS efectivas,
             COUNT(*) FILTER (WHERE ci.estado = 'cancelada') AS canceladas,
             COUNT(*) FILTER (WHERE ci.estado = 'confirmada') AS confirmadas"
        )->first();

        $cal = $this->within(DB::table('calificacion_resena as cr'), 'cr.fecha', $period)
            ->selectRaw('COUNT(*) AS total, AVG(cr.puntuacion) AS promedio')
            ->first();

        return [
            'usuarios_activos' => DB::table('usuario')->where('activo', true)->count(),
            'estudiantes' => DB::table('estudiante as e')->join('usuario as u', 'u.id_usuario', '=', 'e.id_estudiante')->where('u.activo', true)->count(),
            'tutores' => DB::table('tutor as t')->join('usuario as u', 'u.id_usuario', '=', 't.id_tutor')->where('t.habilitado', true)->where('u.activo', true)->count(),
            'solicitudes_pendientes' => DB::table('solicitud_habilitacion')->where('estado', 'pendiente')->count(),
            'cursos' => [
                'BORRADOR' => $cursos['BORRADOR'] ?? 0,
                'PUBLICADO' => $cursos['PUBLICADO'] ?? 0,
                'FINALIZADO' => $cursos['FINALIZADO'] ?? 0,
                'CANCELADO' => $cursos['CANCELADO'] ?? 0,
            ],
            'inscripciones' => (int) $ins->total,
            'inscripciones_efectivas' => (int) $ins->efectivas,
            'cancelaciones' => (int) $ins->canceladas,
            'confirmadas' => (int) $ins->confirmadas,
            'calificaciones' => (int) $cal->total,
            'promedio_calificacion' => $cal->promedio === null ? null : round((float) $cal->promedio, 1),
        ];
    }

    public function topTutors(ReportPeriod $period, int $minRatings, int $limit): array
    {
        $ratings = $this->within(
            DB::table('calificacion_resena as cr')->join('curso as c', 'c.id_curso', '=', 'cr.id_curso'),
            'cr.fecha',
            $period,
        )->groupBy('c.id_tutor')
            ->selectRaw('c.id_tutor, AVG(cr.puntuacion) AS promedio, COUNT(*) AS calificaciones');

        $enrolled = $this->within(
            DB::table('curso_inscripcion as ci')
                ->join('inscripcion as i', 'i.id_inscripcion', '=', 'ci.id_inscripcion')
                ->join('curso as c', 'c.id_curso', '=', 'ci.id_curso')
                ->whereIn('ci.estado', self::EFECTIVAS),
            'i.fecha',
            $period,
        )->groupBy('c.id_tutor')
            ->selectRaw('c.id_tutor, COUNT(*) AS inscritos');

        $courses = DB::table('curso')->where('estado', '<>', 'BORRADOR')
            ->groupBy('id_tutor')
            ->selectRaw('id_tutor, COUNT(*) AS tutorias');

        return DB::table('tutor as t')
            ->join('usuario as u', 'u.id_usuario', '=', 't.id_tutor')
            ->joinSub($ratings, 'r', 'r.id_tutor', '=', 't.id_tutor')
            ->leftJoinSub($enrolled, 'e', 'e.id_tutor', '=', 't.id_tutor')
            ->leftJoinSub($courses, 'k', 'k.id_tutor', '=', 't.id_tutor')
            ->where('t.habilitado', true)
            ->where('u.activo', true)
            ->where('r.calificaciones', '>=', $minRatings)
            ->orderByDesc('r.promedio')
            ->orderByDesc('r.calificaciones')
            ->orderBy('u.apellidos')
            ->limit($limit)
            ->get(['t.id_tutor', 'u.nombres', 'u.apellidos', 'r.promedio', 'r.calificaciones', 'e.inscritos', 'k.tutorias'])
            ->map(fn (object $r): array => [
                'id' => (string) $r->id_tutor,
                'nombre' => trim($r->nombres.' '.$r->apellidos),
                'promedio' => round((float) $r->promedio, 1),
                'calificaciones' => (int) $r->calificaciones,
                'tutorias' => (int) ($r->tutorias ?? 0),
                'inscritos' => (int) ($r->inscritos ?? 0),
            ])
            ->all();
    }

    public function demandByCategory(ReportPeriod $period): array
    {
        $enrolled = $this->within(
            DB::table('curso_inscripcion as ci')
                ->join('inscripcion as i', 'i.id_inscripcion', '=', 'ci.id_inscripcion')
                ->join('curso as c', 'c.id_curso', '=', 'ci.id_curso')
                ->whereIn('ci.estado', self::EFECTIVAS),
            'i.fecha',
            $period,
        )->groupBy('c.id_categoria')
            ->selectRaw('c.id_categoria, COUNT(*) AS inscripciones');

        $courses = DB::table('curso')->where('estado', '<>', 'BORRADOR')
            ->groupBy('id_categoria')
            ->selectRaw('id_categoria, COUNT(*) AS tutorias');

        return DB::table('categoria as cat')
            ->leftJoinSub($enrolled, 'e', 'e.id_categoria', '=', 'cat.id_categoria')
            ->leftJoinSub($courses, 'k', 'k.id_categoria', '=', 'cat.id_categoria')
            ->orderByRaw('COALESCE(e.inscripciones, 0) DESC, COALESCE(k.tutorias, 0) DESC, cat.nombre')
            ->get(['cat.nombre', 'e.inscripciones', 'k.tutorias'])
            ->map(fn (object $r): array => [
                'nombre' => $r->nombre,
                'inscripciones' => (int) ($r->inscripciones ?? 0),
                'tutorias' => (int) ($r->tutorias ?? 0),
            ])
            ->all();
    }

    public function demandByFaculty(ReportPeriod $period): array
    {
        return $this->within(
            DB::table('curso_inscripcion as ci')
                ->join('inscripcion as i', 'i.id_inscripcion', '=', 'ci.id_inscripcion')
                ->join('usuario as u', 'u.id_usuario', '=', 'i.id_usuario')
                ->whereIn('ci.estado', self::EFECTIVAS),
            'i.fecha',
            $period,
        )->groupByRaw("COALESCE(u.facultad, 'Sin registrar')")
            ->selectRaw("COALESCE(u.facultad, 'Sin registrar') AS nombre, COUNT(*) AS inscripciones, COUNT(DISTINCT u.id_usuario) AS estudiantes")
            ->orderByDesc('inscripciones')
            ->orderBy('nombre')
            ->get()
            ->map(fn (object $r): array => [
                'nombre' => $r->nombre,
                'inscripciones' => (int) $r->inscripciones,
                'estudiantes' => (int) $r->estudiantes,
            ])
            ->all();
    }

    public function tutorCourseStats(string $tutorId, ReportPeriod $period): array
    {
        $enrolled = $this->within(
            DB::table('curso_inscripcion as ci')->join('inscripcion as i', 'i.id_inscripcion', '=', 'ci.id_inscripcion'),
            'i.fecha',
            $period,
        )->groupBy('ci.id_curso')
            ->selectRaw(
                "ci.id_curso,
                 COUNT(*) FILTER (WHERE ci.estado IN ('activa','asistio','confirmada')) AS inscritos,
                 COUNT(*) FILTER (WHERE ci.estado = 'confirmada') AS confirmados,
                 COUNT(*) FILTER (WHERE ci.estado = 'cancelada') AS cancelaciones,
                 COUNT(*) FILTER (WHERE ci.estado = 'eliminada') AS retirados"
            );

        $ratings = $this->within(DB::table('calificacion_resena as cr'), 'cr.fecha', $period)
            ->groupBy('cr.id_curso')
            ->selectRaw('cr.id_curso, AVG(cr.puntuacion) AS promedio, COUNT(*) AS calificaciones');

        return DB::table('curso as c')
            ->leftJoin('categoria as cat', 'cat.id_categoria', '=', 'c.id_categoria')
            ->leftJoinSub($enrolled, 'e', 'e.id_curso', '=', 'c.id_curso')
            ->leftJoinSub($ratings, 'r', 'r.id_curso', '=', 'c.id_curso')
            ->where('c.id_tutor', (int) $tutorId)
            ->where('c.estado', '<>', 'BORRADOR')
            ->orderByRaw('c.fecha_inicio DESC NULLS LAST, c.id_curso DESC')
            ->get([
                'c.id_curso', 'c.titulo', 'c.estado', 'cat.nombre as categoria', 'c.cupos', 'c.fecha_inicio', 'c.fecha_finalizacion',
                'e.inscritos', 'e.confirmados', 'e.cancelaciones', 'e.retirados', 'r.promedio', 'r.calificaciones',
            ])
            ->map(fn (object $r): array => [
                'id' => (string) $r->id_curso,
                'titulo' => $r->titulo ?? 'Tutoría sin título',
                'estado' => $r->estado,
                'categoria' => $r->categoria,
                'cupos' => $r->cupos === null ? null : (int) $r->cupos,
                'fecha_inicio' => $r->fecha_inicio,
                'fecha_finalizacion' => $r->fecha_finalizacion,
                'inscritos' => (int) ($r->inscritos ?? 0),
                'confirmados' => (int) ($r->confirmados ?? 0),
                'cancelaciones' => (int) ($r->cancelaciones ?? 0),
                'retirados' => (int) ($r->retirados ?? 0),
                'promedio' => $r->promedio === null ? null : round((float) $r->promedio, 1),
                'calificaciones' => (int) ($r->calificaciones ?? 0),
            ])
            ->all();
    }

    public function tutorDraftCount(string $tutorId): int
    {
        return DB::table('curso')->where('id_tutor', (int) $tutorId)->where('estado', 'BORRADOR')->count();
    }

    private function within(Builder $query, string $column, ReportPeriod $period): Builder
    {
        return $query
            ->when($period->from !== null, fn (Builder $q) => $q->whereDate($column, '>=', $period->from))
            ->when($period->to !== null, fn (Builder $q) => $q->whereDate($column, '<=', $period->to));
    }
}
