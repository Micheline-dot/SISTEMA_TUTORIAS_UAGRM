<?php

namespace App\Business\Inscripciones\Contracts;

interface InscripcionRepository
{
    public function bloquearCurso(string $courseId): ?array;

    public function buscarInscripcionActiva(
        string $studentId,
        string $courseId
    ): ?array;

    public function buscarInscripcionAnterior(
        string $studentId,
        string $courseId
    ): ?array;

    public function contarInscripcionesActivas(string $courseId): int;

    public function crear(string $studentId, string $courseId): int;

    /** CU14: el estudiante cancela su inscripción activa con un motivo. */
    public function cancelar(
        string $studentId,
        string $courseId,
        string $motivo,
    ): bool;

    /** CU15: el tutor retira a un estudiante cuya participación no fue verificada (estado "eliminada"). */
    public function retirar(string $studentId, string $courseId, string $motivo): bool;

    /** ¿El tutor retiró antes a este estudiante de este curso? */
    public function fueRetirado(string $studentId, string $courseId): bool;

    /** CU14: el estudiante confirma su participación después de que el tutor registró su asistencia. */
    public function confirmarParticipacion(string $studentId, string $courseId): bool;

    public function listarPorEstudiante(string $studentId): array;

    /** @return list<array> */
    public function listarActivasPorCurso(string $courseId): array;

    /** @return list<array> inscripciones canceladas o retiradas del curso (historial) */
    public function listarInactivasPorCurso(string $courseId): array;

    public function verificarAsistencia(string $studentId, string $courseId): bool;
}
