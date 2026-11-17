<?php

namespace App\Business\Reportes\Contracts;

use App\Business\Reportes\DTO\ReportPeriod;

/**
 * Consultas agregadas para CU22 (administrador) y CU23 (tutor).
 * "Inscripción efectiva" = curso_inscripcion en activa, asistio o confirmada.
 */
interface ReportRepository
{
    /**
     * @return array{
     *   usuarios_activos: int, estudiantes: int, tutores: int, solicitudes_pendientes: int,
     *   cursos: array<string, int>, inscripciones: int, inscripciones_efectivas: int,
     *   cancelaciones: int, confirmadas: int, calificaciones: int, promedio_calificacion: ?float
     * }
     */
    public function adminTotals(ReportPeriod $period): array;

    /**
     * Tutores ordenados por promedio de calificación (mínimo $minRatings calificaciones).
     *
     * @return list<array{id: string, nombre: string, promedio: float, calificaciones: int, tutorias: int, inscritos: int}>
     */
    public function topTutors(ReportPeriod $period, int $minRatings, int $limit): array;

    /**
     * Inscripciones efectivas por categoría, de mayor a menor.
     *
     * @return list<array{nombre: string, inscripciones: int, tutorias: int}>
     */
    public function demandByCategory(ReportPeriod $period): array;

    /**
     * Inscripciones efectivas por facultad del estudiante, de mayor a menor.
     *
     * @return list<array{nombre: string, inscripciones: int, estudiantes: int}>
     */
    public function demandByFaculty(ReportPeriod $period): array;

    /**
     * Estadísticas por tutoría (sin borradores) de un tutor.
     *
     * @return list<array{
     *   id: string, titulo: string, estado: string, categoria: ?string, cupos: ?int,
     *   fecha_inicio: ?string, fecha_finalizacion: ?string,
     *   inscritos: int, confirmados: int, cancelaciones: int, retirados: int,
     *   promedio: ?float, calificaciones: int
     * }>
     */
    public function tutorCourseStats(string $tutorId, ReportPeriod $period): array;

    /** Cantidad de borradores del tutor (no tienen estadísticas). */
    public function tutorDraftCount(string $tutorId): int;
}
