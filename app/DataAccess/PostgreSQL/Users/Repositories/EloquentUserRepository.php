<?php

namespace App\DataAccess\PostgreSQL\Users\Repositories;

use App\Business\Usuarios\Contracts\UserRepository;
use App\Business\Usuarios\UseCases\RegistrationFailed;
use App\DataAccess\PostgreSQL\Users\Models\User;
use Illuminate\Database\QueryException;

final class EloquentUserRepository implements UserRepository
{
    public function create(
        string $firstNames,
        string $lastNames,
        string $email,
        string $passwordHash,
        ?string $phone,
        ?string $faculty,
        string $registrationType,
        bool $active,
    ): string {
        $user = new User();
        $user->nombres = $firstNames;
        $user->apellidos = $lastNames;
        $user->correo = $email;
        $user->contrasena = $passwordHash;
        $user->telefono = $phone;
        $user->facultad = $faculty;
        $user->tipo_registro = $registrationType;
        $user->activo = $active;

        try {
            $user->save();
        } catch (QueryException $exception) {
            if (UniqueViolation::matches($exception, 'usuario_correo_unique')) {
                throw new RegistrationFailed('This email address is already registered.', previous: $exception);
            }

            throw $exception;
        }

        return PostgreSqlIntegerId::toString($user->getKey());
    }
}
