<?php

namespace App\Business\Cursos\UseCases;

use DomainException;

/** CU07–CU10: regla de negocio de cursos con mensaje listo para mostrar al usuario. */
final class CourseRuleViolation extends DomainException
{
    /** @param list<string> $details */
    public function __construct(string $message, public readonly array $details = [])
    {
        parent::__construct($message);
    }
}
