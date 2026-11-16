<?php

namespace App\DataAccess\Catalogs;

use App\Business\Usuarios\Contracts\FacultyRepository;
use App\Business\Usuarios\DTO\FacultyOption;

/**
 * En el modelo lógico la facultad es un atributo (String) de Usuario,
 * no una tabla. El catálogo de opciones válidas vive en config/catalogos.php.
 */
final class ConfigFacultyRepository implements FacultyRepository
{
    public function exists(string $facultyId): bool
    {
        return in_array($facultyId, $this->names(), true);
    }

    public function listAll(): array
    {
        return array_map(
            static fn (string $name): FacultyOption => new FacultyOption($name, $name),
            $this->names(),
        );
    }

    /** @return list<string> */
    private function names(): array
    {
        return array_values(config('catalogos.facultades', []));
    }
}
