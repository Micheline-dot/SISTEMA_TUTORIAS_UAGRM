<?php

namespace App\Business\Cursos\DTO;

final readonly class ListTutorCoursesInput
{
    public function __construct(public string $tutorId)
    {
    }
}
