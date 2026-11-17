<?php

namespace App\Business\Inscripciones\UseCases;

use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Inscripciones\Contracts\StudentEligibility;
use App\Business\Inscripciones\Exceptions\EnrollmentNotFound;
use App\Business\Inscripciones\Exceptions\EnrollmentRuleViolation;
use App\Business\Inscripciones\Exceptions\StudentNotEligible;
use App\Business\Shared\Contracts\TransactionManager;

/**
 * CU14: el estudiante confirma su participación después de que el tutor
 * registró su asistencia (activa → asistio → confirmada). La participación
 * confirmada habilita calificar al tutor (CU16).
 */
final class ConfirmParticipation
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
            $this->inscripciones->bloquearCurso($courseId);
            $inscripcion = $this->inscripciones->buscarInscripcionActiva($studentId, $courseId);

            if ($inscripcion === null) {
                throw new EnrollmentNotFound();
            }

            $estado = $inscripcion['estado_participacion'] ?? null;

            if ($estado === 'confirmada') {
                throw new EnrollmentRuleViolation('Ya confirmaste tu participación en esta tutoría.');
            }

            if ($estado !== 'asistio' || ! $this->inscripciones->confirmarParticipacion($studentId, $courseId)) {
                throw new EnrollmentRuleViolation('Podrás confirmar tu participación cuando el tutor registre tu asistencia.');
            }
        });
    }
}
