<?php

namespace App\Business\Usuarios\DTO;

/**
 * CU01: datos mínimos del registro público. Teléfono, facultad y registros
 * académicos se completan después en "Mi perfil" (CU02).
 */
final readonly class RegisterUserInput
{
    public function __construct(
        public string $firstNames,
        public string $lastNames,
        public string $email,
        public string $plainTextPassword,
    ) {
    }
}
