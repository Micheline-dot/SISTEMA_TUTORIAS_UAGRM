<?php

namespace App\Business\Cursos\UseCases;

use DomainException;

/** CU07: el tutor debe completar su perfil académico (CU06) antes de crear tutorías. */
final class AcademicProfileIncomplete extends DomainException
{
}
