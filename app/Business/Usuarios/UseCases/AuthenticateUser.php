<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Usuarios\Contracts\SessionAuthenticator;
use App\Business\Usuarios\DTO\AuthenticateUserInput;

final class AuthenticateUser
{
    public function __construct(private readonly SessionAuthenticator $authenticator)
    {
    }

    public function execute(AuthenticateUserInput $input): string
    {
        $userId = $this->authenticator->attempt($input->email, $input->plainTextPassword);

        if ($userId === null) {
            throw new InvalidCredentials('The email or password is incorrect, or the account is inactive.');
        }

        return $userId;
    }
}
