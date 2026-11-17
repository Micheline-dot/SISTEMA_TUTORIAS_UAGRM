<?php

namespace App\DataAccess\PostgreSQL\Calificaciones\Repositories;

use App\Business\Calificaciones\Contracts\RatingRepository;
use App\Business\Calificaciones\DTO\Review;
use App\Business\Calificaciones\Exceptions\RatingRuleViolation;
use App\DataAccess\PostgreSQL\Users\Repositories\UniqueViolation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentRatingRepository implements RatingRepository
{
    public function findByEnrollment(int $enrollmentId, string $courseId): ?array
    {
        $row = DB::table('calificacion_resena as cr')
            ->leftJoin('formulario as f', 'f.id_calificacion', '=', 'cr.id_calificacion')
            ->where('cr.id_inscripcion', $enrollmentId)
            ->where('cr.id_curso', (int) $courseId)
            ->first(['cr.id_calificacion', 'cr.puntuacion', 'cr.comentario', 'f.id_formulario']);

        return $row === null ? null : [
            'id' => (string) $row->id_calificacion,
            'score' => (int) $row->puntuacion,
            'comment' => $row->comentario,
            'hasForm' => $row->id_formulario !== null,
        ];
    }

    public function create(int $enrollmentId, string $courseId, int $score, ?string $comment): string
    {
        try {
            return (string) DB::table('calificacion_resena')->insertGetId([
                'puntuacion' => $score,
                'comentario' => $comment,
                'id_inscripcion' => $enrollmentId,
                'id_curso' => (int) $courseId,
            ], 'id_calificacion');
        } catch (QueryException $e) {
            if (UniqueViolation::matches($e, 'calificacion_resena_inscripcion_unique')) {
                throw new RatingRuleViolation('Ya calificaste esta tutoría.');
            }

            throw $e;
        }
    }

    public function saveForm(string $ratingId, string $title, string $json): bool
    {
        try {
            DB::table('formulario')->insert([
                'titulo' => $title,
                'preguntas' => $json,
                'id_calificacion' => (int) $ratingId,
            ]);

            return true;
        } catch (QueryException $e) {
            if (UniqueViolation::matches($e, 'formulario_calificacion_unique')) {
                return false;
            }

            throw $e;
        }
    }

    public function reviewsForTutor(string $tutorId, ?string $courseId = null): array
    {
        return $this->query($tutorId)
            ->when($courseId !== null, fn ($q) => $q->where('c.id_curso', (int) $courseId))
            ->get()
            ->map(fn (object $r): Review => $this->toReview($r))
            ->all();
    }

    public function latestReviewsForTutor(string $tutorId, int $limit): array
    {
        return $this->query($tutorId)
            ->limit($limit)
            ->get()
            ->map(fn (object $r): Review => $this->toReview($r, withAnswers: false))
            ->all();
    }

    private function query(string $tutorId)
    {
        return DB::table('calificacion_resena as cr')
            ->join('curso as c', 'c.id_curso', '=', 'cr.id_curso')
            ->join('inscripcion as i', 'i.id_inscripcion', '=', 'cr.id_inscripcion')
            ->join('usuario as u', 'u.id_usuario', '=', 'i.id_usuario')
            ->leftJoin('formulario as f', 'f.id_calificacion', '=', 'cr.id_calificacion')
            ->where('c.id_tutor', (int) $tutorId)
            ->orderByDesc('cr.id_calificacion')
            ->select(['cr.id_calificacion', 'cr.puntuacion', 'cr.comentario', 'cr.fecha', 'c.id_curso', 'c.titulo',
                'u.nombres', 'u.apellidos', 'f.preguntas']);
    }

    /** Nombre corto para mostrar: "Ana P." */
    private function toReview(object $r, bool $withAnswers = true): Review
    {
        $answers = null;
        if ($withAnswers && $r->preguntas !== null) {
            $answers = [];
            foreach (json_decode($r->preguntas, true) ?: [] as $item) {
                $answers[$item['clave']] = $item['respuesta'];
            }
        }

        $first = explode(' ', trim((string) $r->nombres))[0];
        $initial = mb_strtoupper(mb_substr(trim((string) $r->apellidos), 0, 1));

        return new Review(
            (string) $r->id_calificacion,
            (string) $r->id_curso,
            $r->titulo ?? 'Tutoría',
            (int) $r->puntuacion,
            $r->comentario,
            date('d/m/Y', strtotime((string) $r->fecha)),
            trim($first.' '.($initial !== '' ? $initial.'.' : '')),
            $answers,
        );
    }
}
