<?php

namespace App\Business\Inscripciones\UseCases;

use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Inscripciones\Contracts\StudentEligibility;
use App\Business\Inscripciones\Exceptions\AlreadyEnrolled;
use App\Business\Inscripciones\Exceptions\AttendanceAlreadyVerified;
use App\Business\Inscripciones\Exceptions\CourseFull;
use App\Business\Inscripciones\Exceptions\CourseNotAvailable;
use App\Business\Inscripciones\Exceptions\StudentNotEligible;
use App\Business\Shared\Contracts\TransactionManager;

final class EnrollStudent
{
    public function __construct(
        private readonly InscripcionRepository $inscripciones,
        private readonly StudentEligibility $studentEligibility,
        private readonly TransactionManager $transactions,
    ) {}

    public function execute(string $studentId, string $courseId): int
    {
        if (! $this->studentEligibility->isEnabledStudent($studentId)) {
            throw new StudentNotEligible();
        }

        return $this->transactions->run(function () use ($studentId, $courseId): int {
            $course = $this->inscripciones->bloquearCurso($courseId);

            if ($course === null || $course['estado'] !== 'PUBLICADO') {
                throw new CourseNotAvailable();
            }

            if ($this->inscripciones->buscarInscripcionActiva($studentId, $courseId) !== null) {
                throw new AlreadyEnrolled();
            }

            if ($this->inscripciones->buscarInscripcionAnterior($studentId, $courseId) !== null) {
                throw new AttendanceAlreadyVerified();
            }

            $capacity = $course['cupos'] === null ? null : (int) $course['cupos'];

            if (
                $capacity !== null
                && $this->inscripciones->contarInscripcionesActivas($courseId) >= $capacity
            ) {
                throw new CourseFull();
            }

            return $this->inscripciones->crear($studentId, $courseId);
        });
    }
}