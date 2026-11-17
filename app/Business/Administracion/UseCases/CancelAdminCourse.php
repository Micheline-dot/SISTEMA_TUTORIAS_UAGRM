<?php

namespace App\Business\Administracion\UseCases;

use App\Business\Administracion\Contracts\AdminCourseRepository;
use App\Business\Administracion\Exceptions\CancellationReasonRequired;
use App\Business\Administracion\Exceptions\CourseCannotBeCancelled;
use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Notificaciones\UseCases\EnviarNotificacion;
use App\Business\Shared\Contracts\TransactionManager;

/**
 * El administrador cancela una tutoría con motivo: se cancelan las
 * inscripciones activas y se avisa (CU19) a los inscritos y al tutor.
 */
final class CancelAdminCourse
{
    public function __construct(
        private readonly AdminCourseRepository $courses,
        private readonly InscripcionRepository $inscripciones,
        private readonly TransactionManager $transactions,
        private readonly EnviarNotificacion $notificar,
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

            $inscritosActivos = array_values(array_map(
                static fn (array $i): string => $i['id_estudiante'],
                array_filter(
                    $this->courses->listarInscritos($courseId),
                    static fn (array $i): bool => $i['estado'] === 'INSCRITO',
                ),
            ));

            if (! $this->courses->cancelarCurso($courseId, $reason)) {
                throw new CourseCannotBeCancelled();
            }

            $this->courses->cancelarInscripcionesActivas($courseId, 'Tutoría cancelada por la administración: '.$reason);

            $titulo = $course['titulo'] ?? 'sin título';

            $this->notificar->aUsuarios(
                $inscritosActivos,
                'Tutoría cancelada',
                "La tutoría \"{$titulo}\" en la que estabas inscrito fue cancelada por la administración. Motivo: {$reason}",
                '/estudiante/mis-inscripciones',
            );

            if (! empty($course['id_tutor'])) {
                $this->notificar->aUsuario(
                    (string) $course['id_tutor'],
                    'Tu tutoría fue cancelada',
                    "La administración canceló tu tutoría \"{$titulo}\". Motivo: {$reason}",
                    '/tutor/mis-tutorias',
                );
            }
        });
    }
}
