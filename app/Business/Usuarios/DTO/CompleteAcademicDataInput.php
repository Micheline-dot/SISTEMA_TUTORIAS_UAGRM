<?php

namespace App\Business\Usuarios\DTO;

final readonly class CompleteAcademicDataInput
{
    public function __construct(
        public string $userId,
        public string $faculty,
        public string $registrationType,
        public ?string $studentRegistry,
        public ?string $assistantRegistry,
        public ?string $teacherCode,
    ) {
    }
}
