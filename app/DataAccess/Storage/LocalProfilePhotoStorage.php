<?php

namespace App\DataAccess\Storage;

use App\Business\Usuarios\Contracts\ProfilePhotoStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fotos de perfil en el disco privado "local" (storage/app/private).
 * Se sirven por una ruta autenticada, sin depender de "storage:link".
 */
final class LocalProfilePhotoStorage implements ProfilePhotoStorage
{
    private const DIRECTORY = 'perfiles/fotos';

    public function store(string $userId, string $temporaryPath, string $extension): string
    {
        $path = self::DIRECTORY.'/'.$userId.'_'.Str::random(16).'.'.$extension;

        $stream = fopen($temporaryPath, 'rb');

        try {
            Storage::disk('local')->writeStream($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $path;
    }

    public function delete(string $storedPath): void
    {
        Storage::disk('local')->delete($storedPath);
    }

    public function exists(string $storedPath): bool
    {
        return Storage::disk('local')->exists($storedPath);
    }
}
