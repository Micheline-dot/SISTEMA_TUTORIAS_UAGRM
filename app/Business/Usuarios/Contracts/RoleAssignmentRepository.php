<?php

namespace App\Business\Usuarios\Contracts;

interface RoleAssignmentRepository
{
    /** Assign one of the roles configured in the approved role catalog. */
    public function assign(string $userId, string $roleName): void;
}
