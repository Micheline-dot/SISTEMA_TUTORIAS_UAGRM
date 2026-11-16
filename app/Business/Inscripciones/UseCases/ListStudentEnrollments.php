<?php

namespace App\Business\Inscripciones\UseCases;

use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Inscripciones\Contracts\StudentEligibility;
use App\Business\Inscripciones\Exceptions\StudentNotEligible;

final class ListStudentEnrollments
{
    public function __construct(
        private readonly InscripcionRepository $inscripciones,
        private readonly StudentEligibility $studentEligibility,
    ) {}

    public function execute(string $studentId): array
    {
        if (! $this->studentEligibility->isEnabledStudent($studentId)) {
            throw new StudentNotEligible();
        }

        return $this->inscripciones->listarPorEstudiante($studentId);
    }
}