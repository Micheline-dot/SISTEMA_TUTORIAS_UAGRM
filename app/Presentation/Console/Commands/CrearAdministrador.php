<?php

namespace App\Presentation\Console\Commands;

use App\Business\Usuarios\DTO\CreateAdministratorInput;
use App\Business\Usuarios\UseCases\CreateAdministrator;
use App\Business\Usuarios\UseCases\InvalidRegistrationData;
use App\Business\Usuarios\UseCases\RegistrationFailed;
use Illuminate\Console\Command;

final class CrearAdministrador extends Command
{
    protected $signature = 'admin:crear';

    protected $description = 'Crea una cuenta de administrador (la contraseña se pide de forma oculta)';

    public function handle(CreateAdministrator $createAdministrator): int
    {
        $nombres = (string) $this->ask('Nombres');
        $apellidos = (string) $this->ask('Apellidos');
        $correo = (string) $this->ask('Correo electrónico');
        $telefono = (string) $this->ask('Teléfono');
        $cargo = (string) $this->ask('Cargo', 'Administrador del sistema');
        $contrasena = (string) $this->secret('Contraseña (mínimo 8 caracteres)');
        $confirmacion = (string) $this->secret('Repite la contraseña');

        if ($contrasena !== $confirmacion) {
            $this->error('Las contraseñas no coinciden. No se creó la cuenta.');

            return self::FAILURE;
        }

        try {
            $id = $createAdministrator->execute(
                new CreateAdministratorInput($nombres, $apellidos, $correo, $contrasena, $telefono, $cargo)
            );
        } catch (InvalidRegistrationData|RegistrationFailed $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Administrador creado (id {$id}). Ya puede iniciar sesión con {$correo}.");

        return self::SUCCESS;
    }
}
