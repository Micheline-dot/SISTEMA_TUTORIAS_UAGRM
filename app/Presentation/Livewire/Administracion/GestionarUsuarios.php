<?php

namespace App\Presentation\Livewire\Administracion;

use App\Business\Usuarios\Exceptions\InvalidProfileData;
use App\Business\Usuarios\Exceptions\ProfileNotFound;
use App\Business\Usuarios\Exceptions\UserManagementRuleViolation;
use App\Business\Usuarios\UseCases\InvalidRegistrationData;
use App\Business\Usuarios\UseCases\ManageUsers;
use App\Business\Usuarios\UseCases\RegisterUser;
use App\Business\Usuarios\UseCases\RegistrationFailed;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/** CU20 Gestionar usuarios (administrador). */
final class GestionarUsuarios extends Component
{
    #[Url(as: 'q', except: '')]
    public string $buscar = '';

    #[Url(except: '')]
    public string $rol = '';

    #[Url(except: '')]
    public string $estado = '';

    #[Url(except: 1)]
    public int $pagina = 1;

    /** '' | crear | editar | degradar */
    public string $modal = '';

    public ?string $usuarioId = null;

    // Formulario crear / editar
    public string $nuevoRol = 'ESTUDIANTE';
    public string $nombres = '';
    public string $apellidos = '';
    public string $correo = '';
    public string $contrasena = '';
    public string $telefono = '';
    public string $cargo = '';

    // Degradar tutor
    public string $motivo = '';

    protected ManageUsers $usuarios;

    public function boot(ManageUsers $usuarios): void
    {
        $this->usuarios = $usuarios;
    }

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['buscar', 'rol', 'estado'], true)) {
            $this->pagina = 1;
        }
    }

    public function irAPagina(int $pagina): void
    {
        $this->pagina = max(1, $pagina);
    }

    public function abrirCrear(): void
    {
        $this->limpiarFormulario();
        $this->modal = 'crear';
    }

    public function abrirEditar(string $id): void
    {
        $this->limpiarFormulario();

        try {
            $u = $this->usuarios->find($id);
        } catch (ProfileNotFound) {
            return;
        }

        $this->usuarioId = $u->id;
        $this->nombres = $u->firstNames;
        $this->apellidos = $u->lastNames;
        $this->correo = $u->email;
        $this->telefono = $u->phone ?? '';
        $this->modal = 'editar';
    }

    public function abrirDegradar(string $id): void
    {
        $this->limpiarFormulario();
        $this->usuarioId = $id;
        $this->modal = 'degradar';
    }

    public function cerrarModal(): void
    {
        $this->limpiarFormulario();
    }

    public function crear(): void
    {
        $esAdmin = $this->nuevoRol === 'ADMINISTRADOR';
        $this->correo = RegisterUser::normalizeEmail($this->correo);

        $this->validate([
            'nuevoRol' => ['required', 'in:ESTUDIANTE,ADMINISTRADOR'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'correo' => array_merge(['required', 'email', 'max:150'], $esAdmin ? [] : ['ends_with:@gmail.com']),
            'contrasena' => ['required', 'string', 'min:8'],
            'telefono' => $esAdmin ? ['required', 'regex:/^\+?[0-9 \-]{7,20}$/'] : ['nullable'],
            'cargo' => $esAdmin ? ['required', 'string', 'max:100'] : ['nullable'],
        ], [
            'correo.ends_with' => 'Las cuentas de estudiante solo aceptan correos @gmail.com.',
            'telefono.regex' => 'Ingresa un teléfono válido.',
        ], [
            'nombres' => 'nombres', 'apellidos' => 'apellidos', 'correo' => 'correo',
            'contrasena' => 'contraseña', 'telefono' => 'teléfono', 'cargo' => 'cargo',
        ]);

        try {
            $this->usuarios->create(
                $this->nuevoRol, $this->nombres, $this->apellidos, $this->correo,
                $this->contrasena, $this->telefono ?: null, $this->cargo ?: null,
            );
        } catch (RegistrationFailed) {
            $this->addError('correo', 'Este correo ya está registrado.');

            return;
        } catch (InvalidRegistrationData) {
            $this->addError('correo', 'Revisa los datos ingresados.');

            return;
        } catch (UserManagementRuleViolation $e) {
            $this->addError('nuevoRol', $e->getMessage());

            return;
        }

        $this->limpiarFormulario();
        session()->flash('ok', 'La cuenta se creó correctamente. Comparte la contraseña inicial con el usuario por un medio seguro.');
    }

    public function guardarEdicion(): void
    {
        $this->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'regex:/^\+?[0-9 \-]{7,20}$/'],
        ], ['telefono.regex' => 'Ingresa un teléfono válido.']);

        try {
            $this->usuarios->update((string) $this->usuarioId, $this->nombres, $this->apellidos, $this->telefono);
        } catch (InvalidProfileData) {
            $this->addError('telefono', 'Revisa los datos ingresados.');

            return;
        } catch (ProfileNotFound) {
            $this->limpiarFormulario();

            return;
        }

        $this->limpiarFormulario();
        session()->flash('ok', 'Los datos del usuario se actualizaron.');
    }

    public function cambiarEstado(string $id, bool $activo): void
    {
        try {
            $this->usuarios->setActive((string) auth()->id(), $id, $activo);
            session()->flash('ok', $activo ? 'La cuenta fue activada.' : 'La cuenta fue desactivada: ya no podrá iniciar sesión.');
        } catch (UserManagementRuleViolation $e) {
            session()->flash('error', $e->getMessage());
        } catch (ProfileNotFound) {
            session()->flash('error', 'El usuario no existe.');
        }
    }

    public function degradar(): void
    {
        $this->validate(['motivo' => ['required', 'string', 'min:5', 'max:500']], [
            'motivo.required' => 'Indica el motivo.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        try {
            $canceladas = $this->usuarios->demoteTutor((string) $this->usuarioId, $this->motivo);
        } catch (UserManagementRuleViolation|ProfileNotFound $e) {
            $this->limpiarFormulario();
            session()->flash('error', $e->getMessage());

            return;
        } catch (InvalidProfileData $e) {
            $this->addError('motivo', 'El motivo debe tener al menos 5 caracteres.');

            return;
        }

        $this->limpiarFormulario();
        session()->flash('ok', 'Se retiró el rol de tutor.'.($canceladas > 0 ? " Se cancelaron {$canceladas} tutoría(s) publicada(s) y se avisó a los inscritos." : ''));
    }

    private function limpiarFormulario(): void
    {
        $this->reset('modal', 'usuarioId', 'nombres', 'apellidos', 'correo', 'contrasena', 'telefono', 'cargo', 'motivo');
        $this->nuevoRol = 'ESTUDIANTE';
        $this->resetValidation();
    }

    public function render(): View
    {
        $resultado = $this->usuarios->list($this->buscar, $this->rol, $this->estado, $this->pagina);
        $paginas = max(1, (int) ceil($resultado['total'] / ManageUsers::PER_PAGE));

        if ($this->pagina > $paginas) {
            $this->pagina = $paginas;
            $resultado = $this->usuarios->list($this->buscar, $this->rol, $this->estado, $this->pagina);
        }

        return view('livewire.administracion.gestionar-usuarios', [
            'usuarios' => $resultado['items'],
            'total' => $resultado['total'],
            'paginas' => $paginas,
            'yo' => (string) auth()->id(),
            'seleccionado' => $this->usuarioId !== null && $this->modal === 'degradar'
                ? rescue(fn () => $this->usuarios->find($this->usuarioId), null, false)
                : null,
        ])->layout('layouts.admin')->title('Gestionar usuarios');
    }
}
