<?php

namespace App\Business\Cursos\DTO;

enum CourseModality: string
{
    case PRESENCIAL = 'PRESENCIAL';
    case VIRTUAL = 'VIRTUAL';
    case HIBRIDA = 'HIBRIDA';
}
