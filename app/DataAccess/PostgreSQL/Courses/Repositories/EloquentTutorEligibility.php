<?php

namespace App\DataAccess\PostgreSQL\Courses\Repositories;

use App\Business\Cursos\Contracts\TutorEligibility;
use App\DataAccess\PostgreSQL\Users\Models\User;
use App\DataAccess\PostgreSQL\Users\Repositories\PostgreSqlIntegerId;

final class EloquentTutorEligibility implements TutorEligibility
{
    public function isEnabledTutor(string $tutorId): bool
    {
        return User::query()
            ->where('id_usuario', PostgreSqlIntegerId::toInteger($tutorId))
            ->where('activo', true)
            ->whereHas('tutor')
            ->exists();
    }
}
