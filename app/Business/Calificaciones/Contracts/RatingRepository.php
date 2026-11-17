<?php

namespace App\Business\Calificaciones\Contracts;

use App\Business\Calificaciones\DTO\Review;

/** Tablas calificacion_resena y formulario. */
interface RatingRepository
{
    /** @return array{id: string, score: int, comment: ?string, hasForm: bool}|null */
    public function findByEnrollment(int $enrollmentId, string $courseId): ?array;

    /** @throws \App\Business\Calificaciones\Exceptions\RatingRuleViolation si ya existe */
    public function create(int $enrollmentId, string $courseId, int $score, ?string $comment): string;

    /** Guarda el formulario (JSON). Devuelve false si esa calificación ya tenía uno. */
    public function saveForm(string $ratingId, string $title, string $json): bool;

    /** @return list<Review> del tutor (opcionalmente de un curso), la más reciente primero */
    public function reviewsForTutor(string $tutorId, ?string $courseId = null): array;

    /** @return list<Review> */
    public function latestReviewsForTutor(string $tutorId, int $limit): array;
}
