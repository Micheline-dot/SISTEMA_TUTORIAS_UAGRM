<?php

namespace App\Business\Usuarios\DTO;

final readonly class RegisterUserInput
{
    public function __construct(
        public string $fullName,
        public string $email,
        public string $plainTextPassword,
        public string $facultyId,
        public string $phone,
    ) {
    }
}
