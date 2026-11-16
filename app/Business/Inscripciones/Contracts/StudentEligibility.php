<?php

namespace App\Business\Inscripciones\Contracts;

interface StudentEligibility
{
    public function isEnabledStudent(string $studentId): bool;
}