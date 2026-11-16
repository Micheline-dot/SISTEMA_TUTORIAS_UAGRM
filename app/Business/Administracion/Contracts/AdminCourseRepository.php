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

    public function cancelarInscripcionesActivas(
        string $courseId
    ): int;
}