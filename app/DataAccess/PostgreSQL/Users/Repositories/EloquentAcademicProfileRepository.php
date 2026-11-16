<?php

namespace App\DataAccess\PostgreSQL\Users\Repositories;

use App\Business\Usuarios\Contracts\AcademicProfileRepository;
use App\DataAccess\PostgreSQL\Users\Models\AcademicProfile;

final class EloquentAcademicProfileRepository implements AcademicProfileRepository
{
    public function ensureForTutor(string $tutorId): void
    {
        AcademicProfile::query()->firstOrCreate([
            'id_tutor' => PostgreSqlIntegerId::toInteger($tutorId),
        ]);
    }
}
