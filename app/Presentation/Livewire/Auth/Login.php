<?php

namespace App\Presentation\Livewire\Auth;

use App\Business\Usuarios\DTO\AuthenticateUserInput;
use App\Business\Usuarios\UseCases\AuthenticateUser;
use App\Business\Usuarios\UseCases\InvalidCredentials;
use Livewire\Component;

final class Login extends Component
{
    public string $email = '';

    public string $password = '';

    private AuthenticateUser $authenticateUser;

    public function boot(AuthenticateUser $authenticateUser): void
    {
        $this->authenticateUser = $authenticateUser;
    }

    public function login()
    {
        $validated = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $this->authenticateUser->execute(new AuthenticateUserInput(
                $validated['email'],
                $validated['password'],
            ));
        } catch (InvalidCredentials) {
            $this->addError('email', 'Correo o contraseña incorrectos, o cuenta inactiva.');

            return null;
        }

        return redirect()->intended(route('home'));
    }

    public function render()
    {
        return view('presentation.auth.login')->layout('layouts.auth');
    }
}
