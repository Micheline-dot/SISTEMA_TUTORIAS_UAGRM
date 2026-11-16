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
        Tutor::query()->updateOrCreate(
            ['id_tutor' => PostgreSqlIntegerId::toInteger($userId)],
            ['tipo_academico' => $academicType, 'habilitado' => true],
        );
    }

    public function revokeTutor(string $userId): bool
    {
        return Tutor::query()
            ->whereKey(PostgreSqlIntegerId::toInteger($userId))
            ->where('habilitado', true)
            ->update(['habilitado' => false]) === 1;
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
            User::ROL_TUTOR => Tutor::query()->where('habilitado', true)->exists(),
            User::ROL_ADMINISTRADOR => Administrator::query()->exists(),
            default => throw new RoleNotConfigured("The {$roleName} role is not configured."),
        };
    }

    public function hasRole(string $userId, string $roleName): bool
    {
        $user = User::query()->find(PostgreSqlIntegerId::toInteger($userId));

        return $user !== null && $user->tieneRol($roleName);
    }

    public function activeUserIdsWithRole(string $roleName): array
    {
        $relation = match ($roleName) {
            User::ROL_ESTUDIANTE => 'estudiante',
            User::ROL_TUTOR => 'tutor',
            User::ROL_ADMINISTRADOR => 'administrador',
            default => throw new RoleNotConfigured("The {$roleName} role is not configured."),
        };

        return User::query()
            ->where('activo', true)
            ->whereHas($relation)
            ->orderBy('id_usuario')
            ->pluck('id_usuario')
            ->map(static fn ($id): string => (string) $id)
            ->all();
    }
}
