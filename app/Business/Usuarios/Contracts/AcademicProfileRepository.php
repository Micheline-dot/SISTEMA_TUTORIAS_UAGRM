<?php

namespace App\Business\Usuarios\Contracts;

interface AcademicProfileRepository
{
    /** Creates the tutor's (empty) academic profile if it does not exist yet. */
    public function ensureForTutor(string $tutorId): void;
}
