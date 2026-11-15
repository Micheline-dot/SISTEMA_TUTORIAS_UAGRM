<?php

namespace App\Business\Usuarios\Contracts;

use App\Business\Usuarios\DTO\FacultyOption;

interface FacultyRepository
{
    public function exists(string $facultyId): bool;

    /** @return list<FacultyOption> */
    public function listAll(): array;
}
