<?php

namespace App\DataAccess\PostgreSQL\Users\Repositories;

use App\Business\Usuarios\Contracts\UserRepository;
use App\Business\Usuarios\UseCases\RegistrationFailed;
use App\DataAccess\PostgreSQL\Users\Models\User;
use Illuminate\Database\QueryException;

final class EloquentUserRepository implements UserRepository
{
    public function create(
        string $fullName,
        string $email,
        string $passwordHash,
        string $phone,
        bool $active,
    ): string {
        $user = new User();
        $user->nombre_completo = $fullName;
        $user->correo = $email;
        $user->contrasena_hash = $passwordHash;
        $user->activo = $active;
        $user->telefono = $phone;

        try {
            $user->save();
        } catch (QueryException $exception) {
            if ($this->isDuplicateEmail($exception)) {
                throw new RegistrationFailed('This email address is already registered.', previous: $exception);
            }

            throw $exception;
        }

        return PostgreSqlIntegerId::toString($user->getKey());
    }

    private function isDuplicateEmail(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;

        return $sqlState === '23505'
            && str_contains($exception->getMessage(), 'usuarios_correo_unique');
    }
}
