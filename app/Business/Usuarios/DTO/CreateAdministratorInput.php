<?php

namespace App\Business\Usuarios\DTO;

final readonly class CreateAdministratorInput
{
    public function __construct(
        public string $firstNames,
        public string $lastNames,
        public string $email,
        public string $plainTextPassword,
        public string $phone,
        public string $position,
    ) {
    }
}
