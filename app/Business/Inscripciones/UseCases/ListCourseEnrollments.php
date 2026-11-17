<?php

namespace App\Business\Inscripciones\UseCases;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Cursos\Policies\CourseOwnershipPolicy;
use App\Business\Cursos\UseCases\CourseNotFound;
use App\Business\Cursos\UseCases\TutorNotEnabled;
use App\Business\Inscripciones\Contracts\InscripcionRepository;

final class ListCourseEnrollments
{
    public function __construct(
        private readonly InscripcionRepository $inscripciones,
        private readonly CourseRepository $courses,
        private readonly TutorEligibility $tutorEligibility,
        private readonly CourseOwnershipPolicy $ownershipPolicy,
    ) {
    }

    /** Inscritos activos (con su estado de participación). */
    public function execute(string $tutorId, string $courseId): array
    {
        $this->courseOwnedBy($tutorId, $courseId);

        return $this->inscripciones->listarActivasPorCurso($courseId);
    }

    /** CU15: historial de inscripciones canceladas por el estudiante o retiradas por el tutor. */
    public function inactive(string $tutorId, string $courseId): array
    {
        $this->courseOwnedBy($tutorId, $courseId);

        return $this->inscripciones->listarInactivasPorCurso($courseId);
    }

    public function courseOwnedBy(string $tutorId, string $courseId): \App\Business\Cursos\DTO\CourseDetails
    {
        if (!$this->tutorEligibility->isEnabledTutor($tutorId)) {
            throw new TutorNotEnabled();
        }

        $course = ctype_digit($courseId) ? $this->courses->findById($courseId) : null;

        if ($course === null) {
            throw new CourseNotFound();
        }

        $this->ownershipPolicy->assertOwner($tutorId, $course);

        return $course;
    }
}