<?php

namespace App\Business\Calificaciones\UseCases;

use App\Business\Calificaciones\Contracts\RatingRepository;
use App\Business\Calificaciones\DTO\Review;
use App\Business\Calificaciones\SatisfactionSurvey;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Cursos\UseCases\TutorNotEnabled;

/**
 * CU18 Ver reseñas y formularios (vista del tutor): promedio, distribución
 * por estrellas, reseñas y resultados agregados de la encuesta.
 */
final class GetTutorReviews
{
    public function __construct(
        private readonly RatingRepository $ratings,
        private readonly TutorEligibility $tutors,
    ) {
    }

    /**
     * @return array{
     *   reviews: list<Review>, average: ?float, count: int, distribution: array<int, int>,
     *   survey: array{responses: int, scales: array<string, array{text: string, average: ?float}>, recommend: ?int, suggestions: list<string>}
     * }
     */
    public function execute(string $tutorId, ?string $courseId = null): array
    {
        if (! $this->tutors->isEnabledTutor($tutorId)) {
            throw new TutorNotEnabled('Only enabled tutors can see their reviews.');
        }

        $reviews = $this->ratings->reviewsForTutor($tutorId, $courseId !== null && ctype_digit($courseId) ? $courseId : null);
        $count = count($reviews);

        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($reviews as $r) {
            $distribution[$r->score]++;
        }

        // Encuesta: promedio por pregunta de escala, % que recomienda, sugerencias.
        $withForm = array_values(array_filter($reviews, fn (Review $r) => $r->answers !== null));
        $scales = [];
        foreach (SatisfactionSurvey::QUESTIONS as $key => $q) {
            if ($q['type'] === 'ESCALA') {
                $values = array_filter(array_map(fn (Review $r) => $r->answers[$key] ?? null, $withForm), 'is_int');
                $scales[$key] = ['text' => $q['text'], 'average' => $values === [] ? null : round(array_sum($values) / count($values), 1)];
            }
        }
        $yes = count(array_filter($withForm, fn (Review $r) => ($r->answers['recomienda'] ?? null) === 'SI'));
        $suggestions = array_values(array_filter(array_map(fn (Review $r) => $r->answers['mejoras'] ?? null, $withForm)));

        return [
            'reviews' => $reviews,
            'average' => $count === 0 ? null : round(array_sum(array_map(fn (Review $r) => $r->score, $reviews)) / $count, 1),
            'count' => $count,
            'distribution' => $distribution,
            'survey' => [
                'responses' => count($withForm),
                'scales' => $scales,
                'recommend' => $withForm === [] ? null : (int) round($yes * 100 / count($withForm)),
                'suggestions' => $suggestions,
            ],
        ];
    }
}
