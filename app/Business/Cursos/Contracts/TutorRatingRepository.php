<?php

namespace App\Business\Cursos\Contracts;

/** Valoración de un tutor a partir de calificacion_resena (CU16). */
interface TutorRatingRepository
{
    /** @return array{average: ?float, count: int} */
    public function summaryForTutor(string $tutorId): array;
}
