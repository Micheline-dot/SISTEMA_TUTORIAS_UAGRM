<?php

namespace App\Business\Usuarios\DTO;

/** CU20: fila de la gestión de usuarios del administrador. */
final readonly class UserSummary
{
    /** @param list<string> $roles ESTUDIANTE / TUTOR / ADMINISTRADOR */
    public function __construct(
        public string $id,
        public string $firstNames,
        public string $lastNames,
        public string $email,
        public ?string $phone,
        public ?string $faculty,
        public string $registrationType,
        public ?string $studentRegistry,
        public array $roles,
        public bool $active,
    ) {
    }

    public function fullName(): string
    {
        return trim($this->firstNames.' '.$this->lastNames);
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }
}
