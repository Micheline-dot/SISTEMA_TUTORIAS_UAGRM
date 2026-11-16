<?php

namespace App\Business\Usuarios\Contracts;

interface ProfilePhotoStorage
{
    /** Copies the temporary file to permanent storage and returns its stored path. */
    public function store(string $userId, string $temporaryPath, string $extension): string;

    public function delete(string $storedPath): void;

    public function exists(string $storedPath): bool;
}
