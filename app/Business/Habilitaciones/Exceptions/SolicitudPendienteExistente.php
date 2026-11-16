<?php

namespace App\Business\Habilitaciones\Exceptions;

use DomainException;

/** CU03: ya existe una solicitud de habilitación pendiente de revisión. */
final class SolicitudPendienteExistente extends DomainException
{
    public function __construct(string $message = 'Ya tienes una solicitud pendiente de revisión. Espera la respuesta del administrador.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
