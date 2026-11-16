<?php

namespace App\DataAccess\PostgreSQL\Inscripciones\Repositories;

use App\Business\Inscripciones\Contracts\StudentEligibility;
use App\DataAccess\PostgreSQL\Users\Models\User;
use App\DataAccess\PostgreSQL\Users\Repositories\PostgreSqlIntegerId;

final class EloquentStudentEligibility implements StudentEligibility
{
    public function isEnabledStudent(string $studentId): bool
    {
        return User::query()
            ->where('id_usuario', PostgreSqlIntegerId::toInteger($studentId))
            ->where('activo', true)
            ->whereHas('estudiante')
            ->exists();
    }
}
