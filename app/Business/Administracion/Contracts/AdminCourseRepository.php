<?php

namespace App\Business\Administracion\Contracts;

interface AdminCourseRepository
{
    public function listar(
        string $search = '',
        string $status = ''
    ): array;

    public function buscarDetalle(string $courseId): ?array;

    public function listarInscritos(string $courseId): array;

    public function cancelarCurso(
        string $courseId,
        string $reason
    ): bool;

    /** Cancela las inscripciones activas guardando el motivo indicado. */
    public function cancelarInscripcionesActivas(
        string $courseId,
        ?string $motivo = null,
    ): int;
}