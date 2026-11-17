<?php

namespace App\Business\Calificaciones\UseCases;

use App\Business\Calificaciones\Contracts\RatingRepository;
use App\Business\Calificaciones\Exceptions\RatingRuleViolation;
use App\Business\Calificaciones\SatisfactionSurvey;
use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Notificaciones\UseCases\EnviarNotificacion;
use App\Business\Shared\Contracts\TransactionManager;

/**
 * CU16 Calificar tutor y reseña (1–5 estrellas + comentario) y
 * CU17 Responder formulario de satisfacción.
 * Solo con participación verificada (asistió y confirmó), una vez por inscripción.
 */
final class RateTutor
{
    public const MIN_COMMENT = 10;
    public const MAX_COMMENT = 1000;

    public function __construct(
        private readonly RatingRepository $ratings,
        private readonly InscripcionRepository $inscripciones,
        private readonly CourseRepository $courses,
        private readonly EnviarNotificacion $notificar,
        private readonly TransactionManager $transactions,
    ) {
    }

    /**
     * Estado de la calificación del estudiante en un curso.
     *
     * @return array{canRate: bool, rating: ?array, reason: ?string, courseTitle: ?string, tutorId: ?string}
     */
    public function status(string $studentId, string $courseId): array
    {
        $course = ctype_digit($courseId) ? $this->courses->findById($courseId) : null;
        $enrollment = $course === null ? null : $this->inscripciones->buscarInscripcionActiva($studentId, $courseId);

        $reason = match (true) {
            $course === null || $enrollment === null => 'No tienes una inscripción activa en esta tutoría.',
            ($enrollment['estado_participacion'] ?? null) !== 'confirmada' => 'Podrás calificar cuando el tutor registre tu asistencia y tú confirmes tu participación.',
            default => null,
        };

        $rating = $reason === null ? $this->ratings->findByEnrollment((int) $enrollment['id_inscripcion'], $courseId) : null;

        return [
            'canRate' => $reason === null && $rating === null,
            'rating' => $rating,
            'reason' => $reason,
            'courseTitle' => $course?->title,
            'tutorId' => $course?->ownerTutorId,
        ];
    }

    public function rate(string $studentId, string $courseId, int $score, ?string $comment): string
    {
        if ($score < 1 || $score > 5) {
            throw new RatingRuleViolation('Elige una calificación de 1 a 5 estrellas.');
        }

        $comment = $comment === null ? '' : trim(preg_replace('/\s+/u', ' ', $comment));

        if (mb_strlen($comment) < self::MIN_COMMENT || mb_strlen($comment) > self::MAX_COMMENT) {
            throw new RatingRuleViolation('Escribe una reseña de entre '.self::MIN_COMMENT.' y '.self::MAX_COMMENT.' caracteres.');
        }

        return $this->transactions->run(function () use ($studentId, $courseId, $score, $comment): string {
            $this->inscripciones->bloquearCurso($courseId);
            $status = $this->status($studentId, $courseId);

            if ($status['reason'] !== null) {
                throw new RatingRuleViolation($status['reason']);
            }

            if ($status['rating'] !== null) {
                throw new RatingRuleViolation('Ya calificaste esta tutoría.');
            }

            $enrollment = $this->inscripciones->buscarInscripcionActiva($studentId, $courseId);
            $id = $this->ratings->create((int) $enrollment['id_inscripcion'], $courseId, $score, $comment);

            $this->notificar->aUsuario(
                (string) $status['tutorId'],
                'Recibiste una nueva calificación',
                'Un estudiante calificó "'.$status['courseTitle'].'" con '.$score.' de 5 estrellas.',
                '/tutor/resenas',
            );

            return $id;
        });
    }

    /** @param array<string, mixed> $answers */
    public function answerSurvey(string $studentId, string $courseId, array $answers): void
    {
        $status = $this->status($studentId, $courseId);

        if ($status['reason'] !== null) {
            throw new RatingRuleViolation($status['reason']);
        }

        if ($status['rating'] === null) {
            throw new RatingRuleViolation('Primero califica al tutor.');
        }

        if ($status['rating']['hasForm']) {
            throw new RatingRuleViolation('Ya respondiste la encuesta de esta tutoría.');
        }

        $clean = [];
        foreach (SatisfactionSurvey::QUESTIONS as $key => $q) {
            $value = $answers[$key] ?? null;

            $clean[$key] = match ($q['type']) {
                'ESCALA' => in_array((int) $value, [1, 2, 3, 4, 5], true) && (string) (int) $value === (string) $value
                    ? (int) $value
                    : throw new RatingRuleViolation('Responde todas las preguntas de la encuesta.'),
                'SI_NO' => in_array($value, ['SI', 'NO'], true)
                    ? $value
                    : throw new RatingRuleViolation('Responde todas las preguntas de la encuesta.'),
                default => mb_substr(trim((string) $value), 0, 1000) ?: null,
            };
        }

        $payload = [];
        foreach (SatisfactionSurvey::QUESTIONS as $key => $q) {
            $payload[] = ['clave' => $key, 'pregunta' => $q['text'], 'tipo' => $q['type'], 'respuesta' => $clean[$key]];
        }

        if (! $this->ratings->saveForm($status['rating']['id'], SatisfactionSurvey::TITLE, json_encode($payload, JSON_UNESCAPED_UNICODE))) {
            throw new RatingRuleViolation('Ya respondiste la encuesta de esta tutoría.');
        }
    }
}
