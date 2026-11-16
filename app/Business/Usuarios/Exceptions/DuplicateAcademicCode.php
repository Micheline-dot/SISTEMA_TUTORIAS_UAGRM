<?php

namespace App\Business\Usuarios\Exceptions;

use DomainException;
use Throwable;

/** El registro/código académico ya pertenece a otro usuario. */
final class DuplicateAcademicCode extends DomainException
{
    public const STUDENT_REGISTRY = 'studentRegistry';
    public const ASSISTANT_REGISTRY = 'assistantRegistry';
    public const TEACHER_CODE = 'teacherCode';

    public function __construct(public readonly string $field, ?Throwable $previous = null)
    {
        parent::__construct("The {$field} is already registered by another user.", 0, $previous);
    }
}
