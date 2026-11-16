<?php

namespace App\Business\Inscripciones\Exceptions;

use RuntimeException;

final class EnrollmentNotFound extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No se encontró una inscripción activa para esta tutoría.');
    }
}