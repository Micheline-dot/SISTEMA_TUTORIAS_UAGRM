<?php

namespace App\Business\Cursos\Contracts;

interface CourseCoverStorage
{
    /** Copia el archivo temporal al almacenamiento permanente y devuelve su ruta. */
    public function store(string $courseId, string $temporaryPath, string $extension): string;

    public function delete(string $storedPath): void;
}
