<?php

namespace App\DataAccess\PostgreSQL\Users\Security;

use App\Business\Usuarios\Contracts\PasswordHasher;
use Illuminate\Support\Facades\Hash;

final class LaravelPasswordHasher implements PasswordHasher
{
    public function hash(string $plainTextPassword): string
    {
        return Hash::make($plainTextPassword);
    }
}
