<?php

namespace App\Business\Cursos\DTO;

/** Represents a field that was sent, including an explicit null value. */
final readonly class FieldChange
{
    public function __construct(public mixed $value)
    {
    }
}
