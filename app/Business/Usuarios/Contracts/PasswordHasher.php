<?php

namespace App\Business\Usuarios\Contracts;

interface PasswordHasher
{
    public function hash(string $plainTextPassword): string;
}
