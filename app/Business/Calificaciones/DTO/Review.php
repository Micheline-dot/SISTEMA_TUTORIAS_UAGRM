<?php

namespace App\Business\Calificaciones\DTO;

/** CU16/CU18: una calificación con su reseña. */
final readonly class Review
{
    /** @param array<string, mixed>|null $answers respuestas de la encuesta (CU17) */
    public function __construct(
        public string $id,
        public string $courseId,
        public string $courseTitle,
        public int $score,
        public ?string $comment,
        public string $date,
        public string $studentName,
        public ?array $answers = null,
    ) {
    }
}
