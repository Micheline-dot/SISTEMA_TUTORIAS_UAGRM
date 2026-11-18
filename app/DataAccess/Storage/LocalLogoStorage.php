<?php

namespace App\DataAccess\Storage;

use App\Business\Configuracion\Contracts\LogoStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Imágenes institucionales (logo, fondo del login) en el disco privado "local" (storage/app/private/sistema). */
final class LocalLogoStorage implements LogoStorage
{
    private const DIRECTORY = 'sistema';

    public function store(string $temporaryPath, string $extension, string $prefix = 'logo'): string
    {
        $prefix = preg_replace('/[^a-z_]/', '', $prefix) ?: 'imagen';
        $path = self::DIRECTORY.'/'.$prefix.'_'.Str::random(16).'.'.$extension;
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
