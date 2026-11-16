<?php

namespace App\Business\Inscripciones\Exceptions;

use RuntimeException;

final class AlreadyEnrolled extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('El estudiante ya tiene una inscripción activa en esta tutoría.');
    }
}