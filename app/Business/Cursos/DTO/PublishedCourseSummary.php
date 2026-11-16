<?php

namespace App\Business\Cursos\DTO;

final readonly class PublishedCourseSummary
{
    public function __construct(
        public string $id,
        public string $tutorId,
        public string $tutorName,
        public ?string $title,
        public ?string $description,
        public ?CourseModality $modality,
        public ?int $totalSeats,
        public ?bool $isFree,
    ) {
    }
}