<?php

namespace App\Business\Inscripciones\Exceptions;

use RuntimeException;

final class CourseFull extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('La tutoría ya no tiene cupos disponibles.');
    }
}