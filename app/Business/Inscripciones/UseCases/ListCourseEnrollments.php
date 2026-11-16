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

    public function execute(string $tutorId, string $courseId): array
    {
        if (!$this->tutorEligibility->isEnabledTutor($tutorId)) {
            throw new TutorNotEnabled();
        }

        $course = $this->courses->findById($courseId);

        if ($course === null) {
            throw new CourseNotFound();
        }

        $this->ownershipPolicy->assertOwner($tutorId, $course);

        return $this->inscripciones->listarActivasPorCurso($courseId);
    }
}