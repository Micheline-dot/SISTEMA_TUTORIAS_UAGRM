<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Usuarios\Contracts\RoleAssignmentRepository;
use App\Business\Usuarios\DTO\CreateAdministratorInput;

/**
 * Crea el primer administrador del sistema solo si todavía no existe ninguno.
 * Es idempotente: si ya hay un administrador, no hace nada y devuelve null.
 */
final class CreateInitialAdministrator
{
    private const ROLE = 'ADMINISTRADOR';

    public function __construct(
        private readonly RoleAssignmentRepository $roles,
        private readonly CreateAdministrator $createAdministrator,
    ) {
    }

    public function execute(CreateAdministratorInput $input): ?string
    {
        if ($this->roles->roleHasUsers(self::ROLE)) {
            return null;
        }

        return $this->createAdministrator->execute($input);
    }
}
