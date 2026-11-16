<?php

namespace App\Business\Usuarios\Contracts;

interface UserRepository
{
    /** Persist an account (tabla usuario) and return its generated identifier as a string. */
    public function create(
        string $firstNames,
        string $lastNames,
        string $email,
        string $passwordHash,
        ?string $phone,
        ?string $faculty,
        string $registrationType,
        bool $active,
    ): string;
}
