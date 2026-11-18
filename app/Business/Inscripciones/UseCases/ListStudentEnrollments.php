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

        // (+) El teléfono del tutor solo se entrega mientras la inscripción está vigente.
        return array_map(function (array $i): array {
            if (($i['estado'] ?? null) !== 'INSCRITO' && isset($i['curso']['tutor'])) {
                $i['curso']['tutor']['telefono'] = null;
            }

            return $i;
        }, $this->inscripciones->listarPorEstudiante($studentId));
    }
}