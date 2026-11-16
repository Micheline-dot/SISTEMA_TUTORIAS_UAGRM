<?php

namespace App\Business\Inscripciones\Exceptions;

use RuntimeException;

final class AttendanceAlreadyVerified extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No se puede realizar esta operación porque la asistencia ya fue verificada.');
    }
}