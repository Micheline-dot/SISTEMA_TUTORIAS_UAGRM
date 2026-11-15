<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Usuarios\Contracts\FacultyRepository;
use App\Business\Usuarios\DTO\FacultyOption;

final class ListFaculties
{
    public function __construct(private readonly FacultyRepository $faculties)
    {
    }

    /** @return list<FacultyOption> */
    public function execute(): array
    {
        return $this->faculties->listAll();
    }
}
