<?php

namespace App\Presentation\Http\Controllers;

use App\Business\Usuarios\UseCases\GetProfilePhoto;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sirve la foto de perfil desde el disco privado a usuarios autenticados
 * (se mostrará también en el perfil público del tutor, CU13).
 */
final class FotoPerfilController
{
    private const MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __invoke(int $userId, GetProfilePhoto $useCase): Response
    {
        $path = $useCase->execute((string) $userId);

        abort_if($path === null, 404);

        $disk = Storage::disk('local');
        $mime = $disk->mimeType($path);

        abort_unless(in_array($mime, self::MIME_TYPES, true), 404);

        return response($disk->get($path), 200, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
