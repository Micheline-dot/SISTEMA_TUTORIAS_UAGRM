<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\SyllabusRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\Policies\CourseOwnershipPolicy;
use App\Business\Shared\Contracts\TransactionManager;

/**
 * CU09 Publicar curso: solo un borrador COMPLETO pasa a PUBLICADO y queda
 * visible en la búsqueda (CU11).
 */
final class PublishCourseDraft
{
    public function __construct(
        private readonly TutorEligibility $tutorEligibility,
        private readonly CourseRepository $courses,
        private readonly CourseOwnershipPolicy $ownership,
        private readonly TransactionManager $transactions,
        private readonly SyllabusRepository $syllabus,
    ) {
    }

    /**
     * Lo que le falta a un borrador para poder publicarse (vacío = listo).
     *
     * @return list<string>
     */
    public function missingRequirements(CourseDetails $course): array
    {
        $missing = [];
        $today = date('Y-m-d');

        if ($course->title === null || trim($course->title) === '') {
            $missing[] = 'Título';
        }
        if ($course->description === null || trim($course->description) === '') {
            $missing[] = 'Descripción';
        }
        if ($course->categoryId === null) {
            $missing[] = 'Categoría';
        }
        if ($course->modality === null) {
            $missing[] = 'Modalidad';
        }
        if ($course->totalSeats === null || $course->totalSeats <= 0) {
            $missing[] = 'Cupos';
        }
        if ($course->isFree === null) {
            $missing[] = 'Gratuito o de pago';
        }
        if ($course->startDate === null) {
            $missing[] = 'Fecha de inicio';
        } elseif ($course->startDate < $today) {
            $missing[] = 'La fecha de inicio no puede ser pasada';
        }
        if ($course->endDate === null) {
            $missing[] = 'Fecha de finalización';
        }
        if ($this->syllabus->countTopics($course->id) === 0) {
            $missing[] = 'Al menos un tema en el temario';
        }

        return $missing;
    }

    public function execute(
        string $courseId,
        string $tutorId,
    ): CourseDetails {
        if (! $this->tutorEligibility->isEnabledTutor($tutorId)) {
            throw new TutorNotEnabled(
                'Only enabled tutors can publish courses.'
            );
        }

        return $this->transactions->run(function () use (
            $courseId,
            $tutorId
        ): CourseDetails {
            $course = $this->courses->findById($courseId);

            if ($course === null) {
                throw new CourseNotFound('Course not found.');
            }

            $this->ownership->assertOwner($tutorId, $course);

            if ($course->status !== CourseStatus::BORRADOR) {
                throw new CourseNotDraft(
                    'Only draft courses can be published.'
                );
            }

            $missing = $this->missingRequirements($course);

            if ($missing !== []) {
                throw new CourseRuleViolation('Completa estos datos antes de publicar.', $missing);
            }

            $published = $this->courses->publishDraftOwnedBy(
                $courseId,
                $tutorId,
            );

            if ($published === null) {
                throw new CourseNotDraft(
                    'The course could not be published.'
                );
            }

            if ($published->status !== CourseStatus::PUBLICADO) {
                throw new \LogicException(
                    'CourseRepository must publish courses in PUBLICADO status.'
                );
            }

            return $published;
        });
    }
}
