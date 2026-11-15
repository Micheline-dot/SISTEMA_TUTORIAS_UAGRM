<?php

namespace App\Business\Usuarios\DTO;

final readonly class FacultyOption
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
