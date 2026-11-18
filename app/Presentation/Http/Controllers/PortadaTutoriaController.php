<?php

namespace App\Presentation\Http\Controllers;

use App\Business\Cursos\UseCases\ManageCourseCover;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/** Sirve la portada de una tutoría desde el disco privado a usuarios autenticados. */
final class PortadaTutoriaController
{
    private const MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __invoke(int $courseId, ManageCourseCover $covers): Response
    {
        $path = $covers->pathFor((string) auth()->id(), (string) $courseId);
        abort_if($path === null, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);

        $mime = $disk->mimeType($path);
        abort_unless(in_array($mime, self::MIME_TYPES, true), 404);

        return response($disk->get($path), 200, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            // Sin caché larga: el tutor puede reemplazar la portada en la misma URL.
            'Cache-Control' => 'private, no-cache',
            'Last-Modified' => gmdate('D, d M Y H:i:s', $disk->lastModified($path)).' GMT',
        ]);
    }
}
