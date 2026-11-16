<?php

namespace App\Business\Cursos\DTO;

/** Optional values for creating an incomplete draft. Dates use Y-m-d. */
final readonly class CourseDraftData
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?CourseModality $modality = null,
        public ?int $totalSeats = null,
        public ?bool $isFree = null,
        public ?string $categoryId = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
    ) {
    }
}
