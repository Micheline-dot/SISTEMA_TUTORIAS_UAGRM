<?php

namespace App\Business\Cursos\DTO;

/** CU08: tema del temario de un curso con sus recursos. */
final readonly class SyllabusTopic
{
    /** @param list<SyllabusResource> $resources */
    public function __construct(
        public string $id,
        public string $name,
        public ?string $date,
        public string $status,
        public array $resources = [],
    ) {
    }

    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETADO';
    }
}
