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
        public ?string $categoryId = null,
        public ?string $categoryName = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?string $cancellationReason = null,
        public int $activeEnrollments = 0,
        /** (+) La tutoría tiene imagen de portada. */
        public bool $hasCover = false,
    ) {
    }
}
