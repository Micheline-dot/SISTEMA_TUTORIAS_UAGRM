<?php

namespace App\Business\Administracion\UseCases;

use App\Business\Administracion\Contracts\AdminCourseRepository;
use App\Business\Administracion\Exceptions\CancellationReasonRequired;
use App\Business\Administracion\Exceptions\CourseCannotBeCancelled;
use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Shared\Contracts\TransactionManager;
final class CancelAdminCourse
{
    public function __construct(
        private readonly AdminCourseRepository $courses,
        private readonly InscripcionRepository $inscripciones,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function execute(string $courseId, string $reason): void
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw new CancellationReasonRequired();
        }

        $this->transactions->run(function () use ($courseId, $reason): void {
            $course = $this->inscripciones->bloquearCurso($courseId);

            if (
                $course === null
                || in_array($course['estado'], ['CANCELADO', 'FINALIZADO'], true)
            ) {
                throw new CourseCannotBeCancelled();
            }

            if (! $this->courses->cancelarCurso($courseId, $reason)) {
                throw new CourseCannotBeCancelled();
            }

            $this->courses->cancelarInscripcionesActivas($courseId);
        });
    }
}
