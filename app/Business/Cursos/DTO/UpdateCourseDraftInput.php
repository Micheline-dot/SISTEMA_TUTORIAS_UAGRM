<?php

namespace App\Business\Cursos\DTO;

final readonly class UpdateCourseDraftInput
{
    public function __construct(
        public string $tutorId,
        public string $courseId,
        public CourseDraftPatch $course,
    ) {
    }
}
