<?php

namespace App\DataAccess\PostgreSQL\Users\Repositories;

use App\Business\Usuarios\Contracts\UserProfileRepository;
use App\Business\Usuarios\DTO\UserProfile;
use App\Business\Usuarios\Exceptions\DuplicateAcademicCode;
use App\DataAccess\PostgreSQL\Users\Models\Student;
use App\DataAccess\PostgreSQL\Users\Models\User;
use Illuminate\Database\QueryException;

final class EloquentUserProfileRepository implements UserProfileRepository
{
    public function find(string $userId): ?UserProfile
    {
        $user = User::query()
            ->with('estudiante')
            ->find(PostgreSqlIntegerId::toInteger($userId));

        if ($user === null) {
            return null;
        }

        return new UserProfile(
            PostgreSqlIntegerId::toString($user->getKey()),
            $user->nombres,
            $user->apellidos,
            $user->correo,
            $user->telefono,
            $user->facultad,
            $user->tipo_registro,
            $user->estudiante?->registro,
            $user->registro_auxiliar,
            $user->codigo_docente,
            $user->foto_perfil,
        );
    }

    public function updatePersonalData(string $userId, string $firstNames, string $lastNames, ?string $phone): void
    {
        User::query()
            ->whereKey(PostgreSqlIntegerId::toInteger($userId))
            ->update([
                'nombres' => $firstNames,
                'apellidos' => $lastNames,
                'telefono' => $phone,
            ]);
    }

    public function updatePhotoPath(string $userId, ?string $photoPath): void
    {
        User::query()
            ->whereKey(PostgreSqlIntegerId::toInteger($userId))
            ->update(['foto_perfil' => $photoPath]);
    }

    public function completeAcademicData(
        string $userId,
        string $faculty,
        string $registrationType,
        ?string $studentRegistry,
        ?string $assistantRegistry,
        ?string $teacherCode,
    ): bool {
        $id = PostgreSqlIntegerId::toInteger($userId);

        try {
            // "WHERE facultad IS NULL" garantiza que solo se registre una vez,
            // incluso con dos envíos simultáneos.
            $updated = User::query()
                ->whereKey($id)
                ->whereNull('facultad')
                ->update([
                    'facultad' => $faculty,
                    'tipo_registro' => $registrationType,
                    'registro_auxiliar' => $assistantRegistry,
                    'codigo_docente' => $teacherCode,
                ]);

            if ($updated !== 1) {
                return false;
            }

            if ($studentRegistry !== null) {
                Student::query()->updateOrCreate(
                    ['id_estudiante' => $id],
                    ['registro' => $studentRegistry],
                );
            }
        } catch (QueryException $exception) {
            throw match (true) {
                UniqueViolation::matches($exception, 'estudiante_registro_unique')
                    => new DuplicateAcademicCode(DuplicateAcademicCode::STUDENT_REGISTRY, $exception),
                UniqueViolation::matches($exception, 'usuario_registro_auxiliar_unique')
                    => new DuplicateAcademicCode(DuplicateAcademicCode::ASSISTANT_REGISTRY, $exception),
                UniqueViolation::matches($exception, 'usuario_codigo_docente_unique')
                    => new DuplicateAcademicCode(DuplicateAcademicCode::TEACHER_CODE, $exception),
                default => $exception,
            };
        }

        return true;
    }
}
