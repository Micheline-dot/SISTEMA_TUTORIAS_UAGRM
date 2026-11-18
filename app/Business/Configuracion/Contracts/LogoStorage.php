<?php

namespace App\Business\Configuracion\Contracts;

/** Imágenes institucionales del sistema (logo, fondo del login). */
interface LogoStorage
{
    /**
     * Copia el archivo temporal al almacenamiento permanente y devuelve su ruta.
     * $prefix identifica la imagen (logo, fondo_login…).
     */
    public function store(string $temporaryPath, string $extension, string $prefix = 'logo'): string;

    public function delete(string $storedPath): void;

    public function exists(string $storedPath): bool;
}
