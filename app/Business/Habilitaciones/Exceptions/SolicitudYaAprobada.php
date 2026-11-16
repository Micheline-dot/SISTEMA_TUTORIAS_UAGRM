<?php

namespace App\Business\Habilitaciones\Exceptions;

use DomainException;

/** CU03: el usuario ya tiene una solicitud aprobada (ya es tutor). */
final class SolicitudYaAprobada extends DomainException
{
    public function __construct(string $message = 'Tu solicitud ya fue aprobada: ya estás habilitado como tutor.')
    {
        parent::__construct($message);
    }
}
