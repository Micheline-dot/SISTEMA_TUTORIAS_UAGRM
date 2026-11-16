<?php

namespace App\DataAccess\PostgreSQL\Users\Repositories;

use App\Business\Usuarios\Contracts\RoleAssignmentRepository;
use App\Business\Usuarios\UseCases\RoleNotConfigured;
use App\DataAccess\PostgreSQL\Users\Models\Role;
use App\DataAccess\PostgreSQL\Users\Models\User;

final class EloquentRoleAssignmentRepository implements RoleAssignmentRepository
{
    public function assign(string $userId, string $roleName): void
    {
        $role = Role::query()
            ->where('nombre', $roleName)
            ->first();

        if ($role === null) {
            throw new RoleNotConfigured(
                "The {$roleName} role is not configured."
            );
        }

        $user = User::query()->findOrFail(
            PostgreSqlIntegerId::toInteger($userId)
        );

        $user->roles()->syncWithoutDetaching([
            $role->getKey(),
        ]);
    }
}