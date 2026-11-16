<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Administracion\Contracts\AdminCourseRepository;
use App\Business\Cursos\Contracts\CourseLifecycleRepository;
use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\Policies\CourseDraftRules;
use App\Business\Cursos\Policies\CourseOwnershipPolicy;
use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Notificaciones\UseCases\EnviarNotificacion;
use App\Business\Shared\Contracts\TransactionManager;

/**
 * CU07 (eliminar borrador) y CU10 Gestionar curso publicado:
 * modificar cupos/fechas, cancelar con motivo y finalizar.
 * Los inscritos reciben aviso de cada cambio (CU19).
 */
final class ManageTutorCourse
{
    public function __construct(
        private readonly CourseRepository $courses,
        private readonly CourseLifecycleRepository $lifecycle,
        private readonly AdminCourseRepository $adminCourses,
        private readonly InscripcionRepository $inscripciones,
        private readonly TutorEligibility $tutors,
        private readonly CourseOwnershipPolicy $ownership,
        private readonly EnviarNotificacion $notificar,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function details(string $tutorId, string $courseId): CourseDetails
    {
        return $this->owned($tutorId, $courseId);
    }

    public function deleteDraft(string $tutorId, string $courseId): void
    {
        $course = $this->owned($tutorId, $courseId);

        if ($course->status !== CourseStatus::BORRADOR) {
            throw new CourseRuleViolation('Solo se pueden eliminar tutorías en borrador. Una publicada se cancela.');
        }

        if (! $this->lifecycle->deleteDraftOwnedBy($courseId, $tutorId)) {
            throw new CourseRuleViolation('No se pudo eliminar el borrador.');
        }
    }

    public function updatePublished(string $tutorId, string $courseId, int $seats, string $startDate, string $endDate): void
    {
        $this->transactions->run(function () use ($tutorId, $courseId, $seats, $startDate, $endDate): void {
            $this->inscripciones->bloquearCurso($courseId);
            $course = $this->owned($tutorId, $courseId);

            if ($course->status !== CourseStatus::PUBLICADO) {
                throw new CourseRuleViolation('Solo se pueden modificar cupos y fechas de una tutoría publicada.');
            }

            if ($seats < 1 || $seats > 1000) {
                throw new CourseRuleViolation('Los cupos deben estar entre 1 y 1000.');
            }

            if ($seats < $course->activeEnrollments) {
                throw new CourseRuleViolation("Hay {$course->activeEnrollments} estudiante(s) inscrito(s): los cupos no pueden ser menos.");
            }

            if (! CourseDraftRules::isValidDate($startDate) || ! CourseDraftRules::isValidDate($endDate)) {
                throw new CourseRuleViolation('Las fechas no son válidas.');
            }

            if ($endDate < $startDate) {
                throw new CourseRuleViolation('La fecha de finalización no puede ser anterior a la de inicio.');
            }

            if ($startDate !== $course->startDate && $startDate < date('Y-m-d')) {
                throw new CourseRuleViolation('La nueva fecha de inicio no puede ser pasada.');
            }

            if (! $this->lifecycle->updatePublishedOwnedBy($courseId, $tutorId, $seats, $startDate, $endDate)) {
                throw new CourseRuleViolation('No se pudo actualizar la tutoría.');
            }

            if ($startDate !== $course->startDate || $endDate !== $course->endDate) {
                $this->notificar->aUsuarios(
                    $this->activeStudents($courseId),
                    'Cambio de fechas en una tutoría',
                    "La tutoría \"{$course->title}\" ahora se realiza del ".self::fmt($startDate).' al '.self::fmt($endDate).'.',
                    '/estudiante/mis-inscripciones',
                );
            }
        });
    }

    public function cancel(string $tutorId, string $courseId, string $reason): void
    {
        $reason = trim($reason);

        if (mb_strlen($reason) < 5 || mb_strlen($reason) > 500) {
            throw new CourseRuleViolation('Indica el motivo de la cancelación (entre 5 y 500 caracteres).');
        }

        $this->transactions->run(function () use ($tutorId, $courseId, $reason): void {
            $this->inscripciones->bloquearCurso($courseId);
            $course = $this->owned($tutorId, $courseId);

            if ($course->status !== CourseStatus::PUBLICADO) {
                throw new CourseRuleViolation('Solo se puede cancelar una tutoría publicada.');
            }

            $students = $this->activeStudents($courseId);

            if (! $this->adminCourses->cancelarCurso($courseId, $reason)) {
                throw new CourseRuleViolation('No se pudo cancelar la tutoría.');
            }

            $this->adminCourses->cancelarInscripcionesActivas($courseId);

            $this->notificar->aUsuarios(
                $students,
                'Tutoría cancelada',
                "El tutor canceló la tutoría \"{$course->title}\". Motivo: {$reason}",
                '/estudiante/mis-inscripciones',
            );
        });
    }

    public function finalize(string $tutorId, string $courseId): void
    {
        $course = $this->owned($tutorId, $courseId);

        if ($course->status !== CourseStatus::PUBLICADO) {
            throw new CourseRuleViolation('Solo se puede finalizar una tutoría publicada.');
        }

        if ($course->startDate !== null && $course->startDate > date('Y-m-d')) {
            throw new CourseRuleViolation('No puedes finalizar una tutoría que todavía no comenzó.');
        }

        if (! $this->lifecycle->finalizeOwnedBy($courseId, $tutorId)) {
            throw new CourseRuleViolation('No se pudo finalizar la tutoría.');
        }

        $this->notificar->aUsuarios(
            $this->activeStudents($courseId),
            'Tutoría finalizada',
            "La tutoría \"{$course->title}\" finalizó. ¡Gracias por participar!",
            '/estudiante/mis-inscripciones',
        );
    }

    private function owned(string $tutorId, string $courseId): CourseDetails
    {
        if (! $this->tutors->isEnabledTutor($tutorId)) {
            throw new TutorNotEnabled('Only enabled tutors can manage courses.');
        }

        $course = ctype_digit($courseId) ? $this->courses->findById($courseId) : null;

        if ($course === null) {
            throw new CourseNotFound('Course was not found.');
        }

        $this->ownership->assertOwner($tutorId, $course);

        return $course;
    }

    /** @return list<string> */
    private function activeStudents(string $courseId): array
    {
        return array_values(array_map(
            static fn (array $i): string => $i['id_estudiante'],
            array_filter($this->adminCourses->listarInscritos($courseId), static fn (array $i): bool => $i['estado'] === 'INSCRITO'),
        ));
    }

    private static function fmt(string $date): string
    {
        return date('d/m/Y', strtotime($date));
    }
}
