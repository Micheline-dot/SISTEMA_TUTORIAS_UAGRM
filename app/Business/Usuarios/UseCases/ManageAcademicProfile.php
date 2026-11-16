<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Usuarios\Contracts\AcademicProfileRepository;
use App\Business\Usuarios\Contracts\RoleAssignmentRepository;
use App\Business\Usuarios\DTO\AcademicProfileData;
use App\Business\Usuarios\Exceptions\InvalidProfileData;
use App\Business\Usuarios\Exceptions\ProfileNotFound;

/**
 * CU06 Gestionar perfil académico (solo tutores habilitados):
 * biografía, enlaces (web, LinkedIn, GitHub…) y áreas de conocimiento.
 */
final class ManageAcademicProfile
{
    public const MAX_BIOGRAPHY = 2000;
    public const MAX_LINKS = 5;
    public const MAX_KNOWLEDGE = 15;

    public function __construct(
        private readonly AcademicProfileRepository $profiles,
        private readonly RoleAssignmentRepository $roles,
    ) {
    }

    public function get(string $tutorId): AcademicProfileData
    {
        $this->assertTutor($tutorId);

        return $this->profiles->findForTutor($tutorId) ?? new AcademicProfileData(null, [], []);
    }

    /**
     * @param list<string> $links
     * @param list<string> $knowledge
     */
    public function save(string $tutorId, ?string $biography, array $links, array $knowledge): void
    {
        $this->assertTutor($tutorId);

        $biography = $biography === null ? null : trim($biography);
        $biography = $biography === '' ? null : $biography;

        if ($biography !== null && mb_strlen($biography) > self::MAX_BIOGRAPHY) {
            throw new InvalidProfileData('The biography cannot exceed '.self::MAX_BIOGRAPHY.' characters.');
        }

        $links = self::cleanList($links);
        if (count($links) > self::MAX_LINKS) {
            throw new InvalidProfileData('At most '.self::MAX_LINKS.' links are allowed.');
        }
        foreach ($links as $link) {
            if (mb_strlen($link) > 255
                || filter_var($link, FILTER_VALIDATE_URL) === false
                || ! preg_match('#^https?://#i', $link)) {
                throw new InvalidProfileData("Invalid link: {$link}");
            }
        }

        $knowledge = self::cleanList($knowledge);
        if (count($knowledge) > self::MAX_KNOWLEDGE) {
            throw new InvalidProfileData('At most '.self::MAX_KNOWLEDGE.' knowledge areas are allowed.');
        }
        foreach ($knowledge as $item) {
            if (mb_strlen($item) < 2 || mb_strlen($item) > 60) {
                throw new InvalidProfileData('Each knowledge area must have between 2 and 60 characters.');
            }
        }

        $this->profiles->saveForTutor($tutorId, new AcademicProfileData($biography, $links, $knowledge));
    }

    private function assertTutor(string $tutorId): void
    {
        if (! $this->roles->hasRole($tutorId, 'TUTOR')) {
            throw new ProfileNotFound('Only enabled tutors have an academic profile.');
        }
    }

    /**
     * Quita vacíos y duplicados (sin distinguir mayúsculas), conserva el orden.
     *
     * @param list<string> $items
     * @return list<string>
     */
    private static function cleanList(array $items): array
    {
        $seen = [];
        $out = [];
        foreach ($items as $item) {
            $item = trim(preg_replace('/\s+/u', ' ', (string) $item));
            $key = mb_strtolower($item);
            if ($item === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $item;
        }

        return $out;
    }
}
