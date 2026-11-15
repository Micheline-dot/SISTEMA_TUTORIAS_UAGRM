<?php

namespace App\Business\Cursos\DTO;

final readonly class CreateCourseDraftInput
{
    public function __construct(
        public string $tutorId,
        public CourseDraftData $course,
    ) {
    }
}
