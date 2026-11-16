<?php

namespace App\Business\Administracion\Exceptions;

use RuntimeException;

final class CancellationReasonRequired extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Debes proporcionar un motivo para cancelar la tutoría.');
    }
}