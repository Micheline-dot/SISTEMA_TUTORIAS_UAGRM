<?php

namespace App\Business\Inscripciones\UseCases;

use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Inscripciones\Contracts\StudentEligibility;
use App\Business\Inscripciones\Exceptions\AttendanceAlreadyVerified;
use App\Business\Inscripciones\Exceptions\CourseNotAvailable;
use App\Business\Inscripciones\Exceptions\EnrollmentNotFound;
use App\Business\Inscripciones\Exceptions\StudentNotEligible;
use App\Business\Shared\Contracts\TransactionManager;

final class CancelEnrollment
{
    public function __construct(
        private readonly InscripcionRepository $inscripciones,
        private readonly StudentEligibility $studentEligibility,
        private readonly TransactionManager $transactions,
    ) {}

    public function execute(string $studentId, string $courseId): void
    {
        if (! $this->studentEligibility->isEnabledStudent($studentId)) {
            throw new StudentNotEligible();
        }

        $this->transactions->run(function () use ($studentId, $courseId): void {
            $course = $this->inscripciones->bloquearCurso($courseId);

            if ($course === null) {
                throw new CourseNotAvailable();
            }

            if ($this->inscripciones->buscarInscripcionAnterior($studentId, $courseId) !== null) {
                throw new AttendanceAlreadyVerified();
            }

            if ($this->inscripciones->buscarInscripcionActiva($studentId, $courseId) === null) {
                throw new EnrollmentNotFound();
            }

            if (! $this->inscripciones->cancelar($studentId, $courseId)) {
                throw new EnrollmentNotFound();
            }
        });
    }
}