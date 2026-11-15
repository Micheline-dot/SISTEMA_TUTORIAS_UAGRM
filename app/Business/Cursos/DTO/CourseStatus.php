<?php

namespace App\Business\Cursos\DTO;

enum CourseStatus: string
{
    case BORRADOR = 'BORRADOR';
    case PUBLICADO = 'PUBLICADO';
    case CANCELADO = 'CANCELADO';
    case FINALIZADO = 'FINALIZADO';
}
