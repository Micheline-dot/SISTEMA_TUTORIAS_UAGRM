<?php

namespace App\DataAccess\PostgreSQL\Users\Repositories;

use Illuminate\Database\QueryException;

/** Detects a PostgreSQL unique violation (SQLSTATE 23505) on a given constraint. */
final class UniqueViolation
{
    public static function matches(QueryException $exception, string $constraint): bool
    {
        return ($exception->errorInfo[0] ?? null) === '23505'
            && str_contains($exception->getMessage(), $constraint);
    }
}
