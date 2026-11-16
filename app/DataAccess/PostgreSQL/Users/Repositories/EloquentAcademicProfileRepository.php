<?php

namespace App\DataAccess\PostgreSQL\Users\Repositories;

use App\Business\Usuarios\Contracts\AcademicProfileRepository;
use App\Business\Usuarios\DTO\AcademicProfileData;
use App\DataAccess\PostgreSQL\Users\Models\AcademicProfile;

/**
 * perfil_academico: biografia (texto), enlaces y conocimiento (texto con un
 * elemento por línea).
 */
final class EloquentAcademicProfileRepository implements AcademicProfileRepository
{
    public function ensureForTutor(string $tutorId): void
    {
        AcademicProfile::query()->firstOrCreate([
            'id_tutor' => PostgreSqlIntegerId::toInteger($tutorId),
        ]);
    }

    public function findForTutor(string $tutorId): ?AcademicProfileData
    {
        $row = AcademicProfile::query()
            ->where('id_tutor', PostgreSqlIntegerId::toInteger($tutorId))
            ->first();

        if ($row === null) {
            return null;
        }

        return new AcademicProfileData(
            $row->biografia,
            self::lines($row->enlaces),
            self::lines($row->conocimiento),
        );
    }

    public function saveForTutor(string $tutorId, AcademicProfileData $profile): void
    {
        AcademicProfile::query()->updateOrCreate(
            ['id_tutor' => PostgreSqlIntegerId::toInteger($tutorId)],
            [
                'biografia' => $profile->biography,
                'enlaces' => $profile->links === [] ? null : implode("\n", $profile->links),
                'conocimiento' => $profile->knowledge === [] ? null : implode("\n", $profile->knowledge),
            ],
        );
    }

    /** @return list<string> */
    private static function lines(?string $text): array
    {
        if ($text === null) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode("\n", $text)), static fn (string $l): bool => $l !== ''));
    }
}
