<?php

namespace App\Business\Inscripciones\UseCases;

use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Inscripciones\Contracts\StudentEligibility;
use App\Business\Inscripciones\Exceptions\AttendanceAlreadyVerified;
use App\Business\Inscripciones\Exceptions\CourseNotAvailable;
use App\Business\Inscripciones\Exceptions\EnrollmentNotFound;
use App\Business\Inscripciones\Exceptions\EnrollmentRuleViolation;
use App\Business\Inscripciones\Exceptions\StudentNotEligible;
use App\Business\Notificaciones\UseCases\EnviarNotificacion;
use App\Business\Shared\Contracts\TransactionManager;

/**
 * CU14 Gestionar inscripción: el estudiante cancela su inscripción indicando
 * un motivo (se libera el cupo y se avisa al tutor). No procede si su
 * asistencia ya fue verificada.
 */
final class CancelEnrollment
{
    public const MIN_MOTIVO = 5;
    public const MAX_MOTIVO = 500;

    public function __construct(
        private readonly InscripcionRepository $inscripciones,
        private readonly StudentEligibility $studentEligibility,
        private readonly TransactionManager $transactions,
        private readonly EnviarNotificacion $notificar,
    ) {}

    public function execute(string $studentId, string $courseId, string $motivo): void
    {
        if (! $this->studentEligibility->isEnabledStudent($studentId)) {
            throw new StudentNotEligible();
        }

        $motivo = trim(preg_replace('/\s+/u', ' ', $motivo));

        if (mb_strlen($motivo) < self::MIN_MOTIVO || mb_strlen($motivo) > self::MAX_MOTIVO) {
            throw new EnrollmentRuleViolation('Indica el motivo de la cancelación (entre 5 y 500 caracteres).');
        }

        $this->transactions->run(function () use ($studentId, $courseId, $motivo): void {
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

            if (! $this->inscripciones->cancelar($studentId, $courseId, $motivo)) {
                throw new EnrollmentNotFound();
            }

            if (! empty($course['id_tutor'])) {
                $this->notificar->aUsuario(
                    (string) $course['id_tutor'],
                    'Un estudiante canceló su inscripción',
                    'Se liberó un cupo en "'.($course['titulo'] ?? 'tu tutoría').'". Motivo: '.$motivo,
                    '/tutor/tutorias/'.$courseId.'/asistencia',
                );
            }
        });
    }
}
