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
        public ?string $categoryName = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public int $activeEnrollments = 0,
        public ?float $tutorRating = null,
        public int $tutorRatingCount = 0,
        public bool $hasCover = false,
        public bool $tutorHasPhoto = false,
    ) {
    }

    public function availableSeats(): ?int
    {
        return $this->totalSeats === null ? null : max(0, $this->totalSeats - $this->activeEnrollments);
    }
}
