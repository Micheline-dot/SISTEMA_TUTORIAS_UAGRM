<?php

namespace App\Business\Habilitaciones\Exceptions;

use DomainException;

/** CU03: el estudiante debe registrar sus datos académicos en "Mi perfil" antes de solicitar ser tutor. */
final class DatosAcademicosIncompletos extends DomainException
{
}
