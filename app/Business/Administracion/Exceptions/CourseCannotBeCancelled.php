<?php

namespace App\Business\Administracion\Exceptions;

use RuntimeException;

final class CourseCannotBeCancelled extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('La tutoría no existe, ya fue cancelada o está finalizada.');
    }
}