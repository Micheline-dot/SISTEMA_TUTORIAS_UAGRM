<?php

namespace App\Business\Cursos\DTO;

final readonly class CourseSummary
{
    public function __construct(
        public string $id,
        public string $ownerTutorId,
        public CourseStatus $status,
        public ?string $title,
        public ?CourseModality $modality,
    ) {
    }
}
