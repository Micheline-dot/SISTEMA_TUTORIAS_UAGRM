<?php

namespace App\Business\Inscripciones\Exceptions;

use RuntimeException;

final class StudentNotEligible extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('El usuario no está habilitado como estudiante.');
    }
}