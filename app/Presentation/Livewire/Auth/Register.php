<?php

namespace App\Presentation\Livewire\Auth;

use App\Business\Usuarios\DTO\RegisterUserInput;
use App\Business\Usuarios\UseCases\InvalidRegistrationData;
use App\Business\Usuarios\UseCases\RegisterUser as RegisterUserUseCase;
use App\Business\Usuarios\UseCases\RegistrationFailed;
use Livewire\Component;

/**
 * CU01 Registro: solo nombres, apellidos, correo @gmail.com y contraseña.
 * Teléfono, facultad y registros académicos se completan en "Mi perfil".
 */
final class Register extends Component
{
    public string $firstNames = '';

    public string $lastNames = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    private RegisterUserUseCase $registerUser;

    public function boot(RegisterUserUseCase $registerUser): void
    {
        $this->registerUser = $registerUser;
    }

    public function register()
    {
        $this->email = RegisterUserUseCase::normalizeEmail($this->email);

        $validated = $this->validate([
            'firstNames' => ['required', 'string', 'max:100'],
            'lastNames' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'ends_with:@'.RegisterUserUseCase::ALLOWED_EMAIL_DOMAIN],
            'password' => ['required', 'string', 'min:'.RegisterUserUseCase::MIN_PASSWORD_LENGTH, 'confirmed'],
        ], [
            'email.ends_with' => 'Solo se aceptan correos @gmail.com.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ], [
            'firstNames' => 'nombres',
            'lastNames' => 'apellidos',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
        ]);

        try {
            $this->registerUser->execute(new RegisterUserInput(
                $validated['firstNames'],
                $validated['lastNames'],
                $validated['email'],
                $validated['password'],
            ));
        } catch (RegistrationFailed) {
            $this->addError('email', 'Este correo electrónico ya está registrado.');

            return null;
        } catch (InvalidRegistrationData) {
            $this->addError('email', 'Revisa los datos: solo se aceptan correos @gmail.com y contraseñas de 8 caracteres o más.');

            return null;
        }

        session()->flash('status', 'Cuenta creada. Inicia sesión y completa tus datos en "Mi perfil".');

        return redirect()->route('login');
    }

    public function render()
    {
        return view('presentation.auth.register')->layout('layouts.auth');
    }
}
