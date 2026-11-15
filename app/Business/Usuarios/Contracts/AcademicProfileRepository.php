<?php

namespace App\Business\Usuarios\Contracts;

interface AcademicProfileRepository
{
    public function create(string $userId, string $facultyId): void;
}
