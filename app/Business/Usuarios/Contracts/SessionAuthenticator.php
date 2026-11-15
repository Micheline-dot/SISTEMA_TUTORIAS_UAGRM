<?php

namespace App\Business\Usuarios\Contracts;

interface SessionAuthenticator
{
    /** Return the authenticated user ID, or null when credentials are invalid. */
    public function attempt(string $email, string $plainTextPassword): ?string;

    public function logout(): void;
}
