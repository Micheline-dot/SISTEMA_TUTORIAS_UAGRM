<?php

namespace App\Business\Cursos\DTO;

final readonly class CourseCatalogFilters
{
    public function __construct(
        public ?string $search = null,
        public ?CourseModality $modality = null,
        public ?bool $isFree = null,
    ) {
    }
}