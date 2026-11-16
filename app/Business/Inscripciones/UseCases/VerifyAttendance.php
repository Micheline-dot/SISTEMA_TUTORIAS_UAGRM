<?php

namespace App\Business\Inscripciones\UseCases;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Cursos\Policies\CourseOwnershipPolicy;
use App\Business\Cursos\UseCases\CourseNotFound;
use App\Business\Cursos\UseCases\TutorNotEnabled;
use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Inscripciones\Exceptions\AttendanceAlreadyVerified;
use App\Business\Inscripciones\Exceptions\EnrollmentNotFound;
use App\Business\Shared\Contracts\TransactionManager;

final class VerifyAttendance
{
    public function __construct(
        private readonly InscripcionRepository $inscripciones,
        private readonly CourseRepository $courses,
        private readonly TutorEligibility $tutorEligibility,
        private readonly CourseOwnershipPolicy $ownershipPolicy,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function execute(
        string $tutorId,
        string $courseId,
        string $studentId
    ): void {
        $this->transactions->run(function () use (
            $tutorId,
            $courseId,
            $studentId
        ): void {
            if (!$this->tutorEligibility->isEnabledTutor($tutorId)) {
                throw new TutorNotEnabled();
            }

            $course = $this->courses->findById($courseId);

            if ($course === null) {
                throw new CourseNotFound();
            }

            $this->ownershipPolicy->assertOwner($tutorId, $course);

            // Serializa las verificaciones que afectan a esta tutoría.
            $lockedCourse = $this->inscripciones->bloquearCurso($courseId);

            if ($lockedCourse === null) {
                throw new CourseNotFound();
            }

            $enrollment = $this->inscripciones->buscarInscripcionActiva(
                $studentId,
                $courseId
            );

            if ($enrollment === null) {
                throw new EnrollmentNotFound();
            }

            if ((bool) $enrollment['asistencia_verificada']) {
                throw new AttendanceAlreadyVerified();
            }

            $updated = $this->inscripciones->verificarAsistencia(
                $studentId,
                $courseId
            );

            if (!$updated) {
                throw new AttendanceAlreadyVerified();
            }
        });
    }
}