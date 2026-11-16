<?php

namespace App\Business\Cursos\Contracts;

/** CU07/CU10: operaciones del tutor sobre el ciclo de vida de su curso. */
interface CourseLifecycleRepository
{
    /** Elimina un BORRADOR del tutor (temario y recursos en cascada). */
    public function deleteDraftOwnedBy(string $courseId, string $tutorId): bool;

    /** Cambia cupos y fechas de un curso PUBLICADO del tutor. */
    public function updatePublishedOwnedBy(string $courseId, string $tutorId, int $seats, string $startDate, string $endDate): bool;

    /** PUBLICADO → FINALIZADO. */
    public function finalizeOwnedBy(string $courseId, string $tutorId): bool;
}
