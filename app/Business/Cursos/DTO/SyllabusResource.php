<?php

namespace App\Business\Cursos\DTO;

/** CU08: recurso (material) de un tema: nombre + enlace. */
final readonly class SyllabusResource
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $url,
    ) {
    }
}
