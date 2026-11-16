<?php

namespace App\Business\Usuarios\DTO;

/** CU02: datos del perfil tal como los ve su dueño. */
final readonly class UserProfile
{
    public function __construct(
        public string $userId,
        public string $firstNames,
        public string $lastNames,
        public string $email,
        public ?string $phone,
        public ?string $faculty,
        public string $registrationType,
        public ?string $studentRegistry,
        public ?string $assistantRegistry,
        public ?string $teacherCode,
        public ?string $photoPath,
    ) {
    }

    /** Facultad y tipo de registro se completan una sola vez. */
    public function academicDataCompleted(): bool
    {
        return $this->faculty !== null;
    }

    public function isAdministrator(): bool
    {
        return $this->registrationType === 'ADMINISTRADOR';
    }

    public function hasPhoto(): bool
    {
        return $this->photoPath !== null;
    }
}
