<?php

namespace App\Business\Usuarios\Contracts;

use App\Business\Usuarios\DTO\AcademicProfileData;

interface AcademicProfileRepository
{
    /** Creates the tutor's (empty) academic profile if it does not exist yet. */
    public function ensureForTutor(string $tutorId): void;

    public function findForTutor(string $tutorId): ?AcademicProfileData;

    public function saveForTutor(string $tutorId, AcademicProfileData $profile): void;
}
