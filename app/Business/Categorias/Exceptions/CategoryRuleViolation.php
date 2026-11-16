<?php

namespace App\Business\Categorias\Exceptions;

use DomainException;

/** CU21: regla de negocio de categorías (mensaje listo para mostrar). */
final class CategoryRuleViolation extends DomainException
{
}
