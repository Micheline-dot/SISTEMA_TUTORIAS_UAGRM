<?php

namespace App\DataAccess\PostgreSQL\Users\Repositories;

use App\Business\Usuarios\Contracts\FacultyRepository;
use App\Business\Usuarios\DTO\FacultyOption;
use App\DataAccess\PostgreSQL\Users\Models\Faculty;

final class EloquentFacultyRepository implements FacultyRepository
{
    public function exists(string $facultyId): bool
    {
        return Faculty::query()->whereKey(PostgreSqlIntegerId::toInteger($facultyId))->exists();
    }

    public function listAll(): array
    {
        return Faculty::query()
            ->orderBy('nombre')
            ->get(['id_facultad', 'nombre'])
            ->map(static fn (Faculty $faculty): FacultyOption => new FacultyOption(
                PostgreSqlIntegerId::toString($faculty->getKey()),
                $faculty->nombre,
            ))
            ->all();
    }
}
