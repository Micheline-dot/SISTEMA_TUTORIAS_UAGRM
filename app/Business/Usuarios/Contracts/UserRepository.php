<?php

namespace App\Business\Usuarios\Contracts;

interface UserRepository
{
    /** Persist an account and return its generated identifier as a string. */
    public function create(
        string $fullName,
        string $email,
        string $passwordHash,
        string $phone,
        bool $active,
    ): string;
}
