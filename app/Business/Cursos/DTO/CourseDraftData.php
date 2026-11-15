<?php

namespace App\Business\Cursos\DTO;

/** Optional values for creating an incomplete draft. */
final readonly class CourseDraftData
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?CourseModality $modality = null,
        public ?int $totalSeats = null,
        public ?bool $isFree = null,
    ) {
    }
}
