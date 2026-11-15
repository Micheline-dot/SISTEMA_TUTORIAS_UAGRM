<?php

namespace App\Business\Cursos\DTO;

final readonly class CourseDetails
{
    public function __construct(
        public string $id,
        public string $ownerTutorId,
        public CourseStatus $status,
        public ?string $title,
        public ?string $description,
        public ?CourseModality $modality,
        public ?int $totalSeats,
        public ?bool $isFree,
    ) {
    }
}
