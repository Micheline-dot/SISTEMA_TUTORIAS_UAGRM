<?php

namespace App\Business\Cursos\Contracts;

interface TutorEligibility
{
    public function isEnabledTutor(string $tutorId): bool;
}
