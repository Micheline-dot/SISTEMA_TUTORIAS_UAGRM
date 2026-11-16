<?php

namespace App\DataAccess\PostgreSQL\Users\Repositories;

use App\Business\Usuarios\Contracts\RoleAssignmentRepository;
use App\Business\Usuarios\UseCases\RegistrationFailed;
use App\Business\Usuarios\UseCases\RoleNotConfigured;
use App\DataAccess\PostgreSQL\Users\Models\Administrator;
use App\DataAccess\PostgreSQL\Users\Models\Student;
use App\DataAccess\PostgreSQL\Users\Models\Tutor;
use App\DataAccess\PostgreSQL\Users\Models\User;
use Illuminate\Database\QueryException;

final class EloquentRoleAssignmentRepository implements RoleAssignmentRepository
{
    public function assignStudent(string $userId, ?string $registry): void
    {
        try {
            Student::query()->create([
                'id_estudiante' => PostgreSqlIntegerId::toInteger($userId),
                'registro' => $registry,
            ]);
        } catch (QueryException $exception) {
            if (UniqueViolation::matches($exception, 'estudiante_registro_unique')) {
                throw new RegistrationFailed('This student registry is already registered.', previous: $exception);
            }

            throw $exception;
        }
    }

    public function assignTutor(string $userId, string $academicType): void
    {
        Tutor::query()->firstOrCreate(
            ['id_tutor' => PostgreSqlIntegerId::toInteger($userId)],
            ['tipo_academico' => $academicType],
        );
    }

    public function assignAdministrator(string $userId, string $position): void
    {
        Administrator::query()->create([
            'id_administrador' => PostgreSqlIntegerId::toInteger($userId),
            'cargo' => $position,
        ]);
    }

    public function roleHasUsers(string $roleName): bool
    {
        return match ($roleName) {
            User::ROL_ESTUDIANTE => Student::query()->exists(),
            User::ROL_TUTOR => Tutor::query()->exists(),
            User::ROL_ADMINISTRADOR => Administrator::query()->exists(),
            default => throw new RoleNotConfigured("The {$roleName} role is not configured."),
        };
    }
}
