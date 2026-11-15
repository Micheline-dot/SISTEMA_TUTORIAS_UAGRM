<?php

namespace App\Business\Usuarios\DTO;

final readonly class AuthenticateUserInput
{
    public function __construct(
        public string $email,
        public string $plainTextPassword,
    ) {
    }
}
