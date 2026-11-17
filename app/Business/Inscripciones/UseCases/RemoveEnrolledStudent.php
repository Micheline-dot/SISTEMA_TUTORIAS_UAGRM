<?php

namespace App\Business\Inscripciones\UseCases;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\Policies\CourseOwnershipPolicy;
use App\Business\Cursos\UseCases\CourseNotFound;
use App\Business\Cursos\UseCases\TutorNotEnabled;
use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Inscripciones\Exceptions\AttendanceAlreadyVerified;
use App\Business\Inscripciones\Exceptions\EnrollmentNotFound;
use App\Business\Inscripciones\Exceptions\EnrollmentRuleViolation;
use App\Business\Notificaciones\UseCases\EnviarNotificacion;
use App\Business\Shared\Contracts\TransactionManager;

/**
 * CU15 Gestionar estudiantes inscritos: el tutor retira con motivo a un
 * estudiante cuya participación NO fue verificada. Queda en estado
 * "eliminada", se libera el cupo y se avisa al estudiante.
 */
final class RemoveEnrolledStudent
{
    public function __construct(
        private readonly InscripcionRepository $inscripciones,
        private readonly CourseRepository $courses,
        private readonly TutorEligibility $tutorEligibility,
        private readonly CourseOwnershipPolicy $ownershipPolicy,
        private readonly TransactionManager $transactions,
        private readonly EnviarNotificacion $notificar,
    ) {
    }

    public function execute(string $tutorId, string $courseId, string $studentId, string $motivo): void
    {
        $motivo = trim(preg_replace('/\s+/u', ' ', $motivo));

        if (mb_strlen($motivo) < 5 || mb_strlen($motivo) > 500) {
            throw new EnrollmentRuleViolation('Indica el motivo del retiro (entre 5 y 500 caracteres).');
        }

        $this->transactions->run(function () use ($tutorId, $courseId, $studentId, $motivo): void {
            if (! $this->tutorEligibility->isEnabledTutor($tutorId)) {
                throw new TutorNotEnabled();
            }

            $course = $this->courses->findById($courseId);

            if ($course === null) {
                throw new CourseNotFound();
            }

            $this->ownershipPolicy->assertOwner($tutorId, $course);

            if ($course->status !== CourseStatus::PUBLICADO) {
                throw new EnrollmentRuleViolation('Solo puedes retirar estudiantes de una tutoría publicada.');
            }

            $this->inscripciones->bloquearCurso($courseId);
            $inscripcion = $this->inscripciones->buscarInscripcionActiva($studentId, $courseId);

            if ($inscripcion === null) {
                throw new EnrollmentNotFound();
            }

            if ((bool) $inscripcion['asistencia_verificada']) {
                throw new AttendanceAlreadyVerified();
            }

            if (! $this->inscripciones->retirar($studentId, $courseId, $motivo)) {
                throw new AttendanceAlreadyVerified();
            }

            $this->notificar->aUsuario(
                $studentId,
                'Fuiste retirado de una tutoría',
                'El tutor te retiró de "'.($course->title ?? 'la tutoría').'". Motivo: '.$motivo,
                '/estudiante/mis-inscripciones',
            );
        });
    }
}
