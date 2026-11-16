<?php

namespace App\Business\Usuarios\Contracts;

use App\Business\Usuarios\DTO\UserProfile;

interface UserProfileRepository
{
    public function find(string $userId): ?UserProfile;

    public function updatePersonalData(string $userId, string $firstNames, string $lastNames, ?string $phone): void;

    public function updatePhotoPath(string $userId, ?string $photoPath): void;

    /**
     * Saves faculty, registration type and registries only if they were not
     * completed before (facultad IS NULL). Returns false when they already were.
     *
     * @throws \App\Business\Usuarios\Exceptions\DuplicateAcademicCode
     */
    public function completeAcademicData(
        string $userId,
        string $faculty,
        string $registrationType,
        ?string $studentRegistry,
        ?string $assistantRegistry,
        ?string $teacherCode,
    ): bool;
}
