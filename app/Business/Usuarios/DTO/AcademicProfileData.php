<?php

namespace App\Business\Usuarios\DTO;

/** CU06: perfil académico del tutor (tabla perfil_academico). */
final readonly class AcademicProfileData
{
    /**
     * @param list<string> $links
     * @param list<string> $knowledge
     */
    public function __construct(
        public ?string $biography,
        public array $links,
        public array $knowledge,
    ) {
    }

    /** Completo = tiene biografía y al menos un conocimiento (se exigirá para crear cursos, CU07). */
    public function isComplete(): bool
    {
        return $this->biography !== null && trim($this->biography) !== '' && $this->knowledge !== [];
    }
}
