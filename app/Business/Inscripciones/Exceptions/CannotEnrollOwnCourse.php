<?php

namespace App\Business\Inscripciones\Exceptions;

use DomainException;

/** Un tutor no puede inscribirse en su propia tutoría. */
final class CannotEnrollOwnCourse extends DomainException
{
    public function __construct()
    {
        parent::__construct('No puedes inscribirte en una tutoría que tú impartes.');
    }
}
