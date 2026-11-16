<?php

namespace App\Business\Usuarios\Contracts;

/**
 * Los roles del sistema son las especializaciones de Usuario del modelo
 * lógico: Estudiante, Tutor y Administrador.
 */
interface RoleAssignmentRepository
{
    /** Registers the user as Estudiante; the registry may be completed later in the profile. */
    public function assignStudent(string $userId, ?string $registry): void;

    /** Registers the user as Tutor (idempotent) with its academic type. */
    public function assignTutor(string $userId, string $academicType): void;

    /** Registers the user as Administrador with its position. */
    public function assignAdministrator(string $userId, string $position): void;

    /** Whether at least one account currently holds the given role. */
    public function roleHasUsers(string $roleName): bool;
}
