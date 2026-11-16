<?php

namespace App\Business\Usuarios\Exceptions;

use DomainException;

/** CU20: regla de negocio de la gestión de usuarios (mensaje listo para mostrar). */
final class UserManagementRuleViolation extends DomainException
{
}
