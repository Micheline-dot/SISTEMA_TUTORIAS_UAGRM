<?php

namespace App\DataAccess\PostgreSQL\Courses\Repositories;

use App\Business\Cursos\Contracts\CourseLifecycleRepository;
use App\DataAccess\PostgreSQL\Courses\Models\Course;
use App\DataAccess\PostgreSQL\Users\Repositories\PostgreSqlIntegerId;

final class EloquentCourseLifecycleRepository implements CourseLifecycleRepository
{
    public function deleteDraftOwnedBy(string $courseId, string $tutorId): bool
    {
        return $this->owned($courseId, $tutorId, 'BORRADOR')->delete() === 1;
    }

    public function updatePublishedOwnedBy(string $courseId, string $tutorId, int $seats, string $startDate, string $endDate): bool
    {
        return $this->owned($courseId, $tutorId, 'PUBLICADO')->update([
            'cupos' => $seats,
            'fecha_inicio' => $startDate,
            'fecha_finalizacion' => $endDate,
        ]) === 1;
    }

    public function finalizeOwnedBy(string $courseId, string $tutorId): bool
    {
        return $this->owned($courseId, $tutorId, 'PUBLICADO')->update(['estado' => 'FINALIZADO']) === 1;
    }

    private function owned(string $courseId, string $tutorId, string $status)
    {
        return Course::query()
            ->where('id_curso', PostgreSqlIntegerId::toInteger($courseId))
            ->where('id_tutor', PostgreSqlIntegerId::toInteger($tutorId))
            ->where('estado', $status);
    }
}
