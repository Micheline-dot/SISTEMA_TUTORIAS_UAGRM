<?php

namespace App\Presentation\Livewire\Auth;

use App\Business\Usuarios\DTO\RegisterUserInput;
use App\Business\Usuarios\UseCases\FacultyNotFound;
use App\Business\Usuarios\UseCases\ListFaculties;
use App\Business\Usuarios\UseCases\RegisterUser as RegisterUserUseCase;
use App\Business\Usuarios\UseCases\RegistrationFailed;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Register extends Component
{
    public string $fullName = '';

    public string $email = '';

    public string $password = '';

    public string $facultyId = '';

    public string $phone = '';

    private RegisterUserUseCase $registerUser;

    private ListFaculties $listFaculties;

    public function boot(RegisterUserUseCase $registerUser, ListFaculties $listFaculties): void
    {
        $this->registerUser = $registerUser;
        $this->listFaculties = $listFaculties;
    }

    #[Computed]
    public function faculties(): array
    {
        return array_map(
            static fn ($option): array => ['id' => $option->id, 'name' => $option->name],
            $this->listFaculties->execute(),
        );
    }

    public function register()
    {
        $validated = $this->validate([
            'fullName' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'facultyId' => ['required', 'integer', 'min:1'],
            'phone' => ['required', 'string'],
        ]);

        try {
            $this->registerUser->execute(new RegisterUserInput(
                $validated['fullName'],
                $validated['email'],
                $validated['password'],
                $validated['facultyId'],
                $validated['phone'],
            ));
        } catch (RegistrationFailed $exception) {
            $this->addError('email', $exception->getMessage());

            return null;
        } catch (FacultyNotFound) {
            $this->addError('facultyId', 'La facultad seleccionada no pertenece al catálogo disponible.');

            return null;
        }

        session()->flash('status', 'Perfil registrado. Inicia sesión con tu correo y contraseña.');

        return redirect()->route('login');
    }

    public function render()
    {
        return view('presentation.auth.register')->layout('layouts.auth');
    }
}
