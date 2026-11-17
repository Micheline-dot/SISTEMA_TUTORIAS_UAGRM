<?php

namespace App\Business\Cursos\DTO;

/** CU13 Visualizar detalles del curso (vista del estudiante). */
final readonly class PublicCourseDetail
{
    /**
     * @param list<SyllabusTopic> $topics
     * @param list<string> $tutorKnowledge
     * @param list<string> $tutorLinks
     */
    public function __construct(
        public CourseDetails $course,
        public array $topics,
        public string $tutorId,
        public string $tutorName,
        public ?string $tutorFaculty,
        public bool $tutorHasPhoto,
        public ?string $tutorBiography,
        public array $tutorKnowledge,
        public array $tutorLinks,
        public ?float $tutorRating,
        public int $tutorRatingCount,
        public bool $viewerIsEnrolled,
        public bool $viewerIsOwner,
        public bool $viewerAlreadyParticipated,
    ) {
    }

    public function availableSeats(): ?int
    {
        return $this->course->totalSeats === null
            ? null
            : max(0, $this->course->totalSeats - $this->course->activeEnrollments);
    }

    /** Los enlaces de los recursos solo se muestran a inscritos y al propio tutor. */
    public function canSeeResourceLinks(): bool
    {
        return $this->viewerIsEnrolled || $this->viewerIsOwner || $this->viewerAlreadyParticipated;
    }
}
