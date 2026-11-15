<?php

namespace App\DataAccess\PostgreSQL\Users\Repositories;

use App\Business\Usuarios\Contracts\AcademicProfileRepository;
use App\DataAccess\PostgreSQL\Users\Models\AcademicProfile;

final class EloquentAcademicProfileRepository implements AcademicProfileRepository
{
    public function create(string $userId, string $facultyId): void
    {
        AcademicProfile::query()->create([
            'id_usuario' => PostgreSqlIntegerId::toInteger($userId),
            'id_facultad' => PostgreSqlIntegerId::toInteger($facultyId),
        ]);
    }
}
