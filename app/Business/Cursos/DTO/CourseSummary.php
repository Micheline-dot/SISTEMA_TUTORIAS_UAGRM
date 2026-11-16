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
        public ?string $categoryName = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?int $totalSeats = null,
        public int $activeEnrollments = 0,
    ) {
    }
}
