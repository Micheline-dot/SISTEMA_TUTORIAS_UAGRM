<?php

namespace App\Business\Categorias\DTO;

final readonly class Category
{
    public function __construct(
        public string $id,
        public string $name,
        public int $courseCount = 0,
    ) {
    }
}
