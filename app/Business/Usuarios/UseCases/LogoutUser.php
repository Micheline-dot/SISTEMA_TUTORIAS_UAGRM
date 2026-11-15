<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Usuarios\Contracts\SessionAuthenticator;

final class LogoutUser
{
    public function __construct(private readonly SessionAuthenticator $authenticator)
    {
    }

    public function execute(): void
    {
        $this->authenticator->logout();
    }
}
