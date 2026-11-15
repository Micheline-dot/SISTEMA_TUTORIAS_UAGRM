<?php

namespace App\Business\Cursos\DTO;

final readonly class TutorCourseDetailsInput
{
    public function __construct(
        public string $tutorId,
        public string $courseId,
    ) {
    }
}
