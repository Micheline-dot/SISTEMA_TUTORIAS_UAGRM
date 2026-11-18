<?php

namespace App\DataAccess\Storage;

use App\Business\Cursos\Contracts\CourseCoverStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Portadas de tutorías en el disco privado "local" (storage/app/private/cursos/portadas). */
final class LocalCourseCoverStorage implements CourseCoverStorage
{
    private const DIRECTORY = 'cursos/portadas';

    public function store(string $courseId, string $temporaryPath, string $extension): string
    {
        $path = self::DIRECTORY.'/'.$courseId.'_'.Str::random(16).'.'.$extension;
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
}
