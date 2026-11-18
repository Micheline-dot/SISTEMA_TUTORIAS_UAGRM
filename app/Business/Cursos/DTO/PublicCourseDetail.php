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
        /** @var list<\App\Business\Calificaciones\DTO\Review> últimas reseñas del tutor (CU16) */
        public array $latestReviews = [],
        /** (+) Teléfono del tutor: solo se entrega a estudiantes inscritos. */
        public ?string $tutorPhone = null,
    ) {
    }

    public function availableSeats(): ?int
    {
        return $this->course->totalSeats === null
            ? null
            : max(0, $this->course->totalSeats - $this->course->activeEnrollments);
    }

    /** Enlace de WhatsApp al tutor (números bolivianos de 8 dígitos llevan el prefijo 591). */
    public function tutorWhatsappUrl(): ?string
    {
        return self::whatsappUrl($this->tutorPhone);
    }

    public static function whatsappUrl(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '' || strlen($digits) < 7) {
            return null;
        }
        if (strlen($digits) === 8) {
            $digits = '591'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }

    /** Los enlaces de los recursos solo se muestran a inscritos y al propio tutor. */
    public function canSeeResourceLinks(): bool
    {
        return $this->viewerIsEnrolled || $this->viewerIsOwner || $this->viewerAlreadyParticipated;
    }
}
