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

    public function cancelar(
        string $studentId,
        string $courseId
    ): bool;

    public function listarPorEstudiante(string $studentId): array;

    /** @return list<array> */
    public function listarActivasPorCurso(string $courseId): array;

    public function verificarAsistencia(string $studentId, string $courseId): bool;
}
