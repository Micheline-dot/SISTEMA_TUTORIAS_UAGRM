<?php

namespace App\Business\Inscripciones\Exceptions;

use RuntimeException;

final class CourseNotAvailable extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('La tutoría no existe o no está publicada.');
    }
}