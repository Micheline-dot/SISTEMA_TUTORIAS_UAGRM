<?php

namespace App\Presentation\Http\Controllers;

use App\Business\Configuracion\UseCases\ManageLoginBackground;
use App\Business\Configuracion\UseCases\ManageSystemImage;
use App\Business\Configuracion\UseCases\ManageSystemLogo;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sirve las imágenes institucionales (logo y fondo del login). Son públicas
 * porque se muestran en el login. La URL lleva ?v=<hash de la ruta>, así que
 * puede cachearse: al cambiar la imagen cambia la URL.
 */
final class LogoSistemaController
{
    private const MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function logo(ManageSystemLogo $images): Response
    {
        return $this->serve($images);
    }

    public function fondoLogin(ManageLoginBackground $images): Response
    {
        return $this->serve($images);
    }

    private function serve(ManageSystemImage $images): Response
    {
        $path = $images->currentPath();
        abort_if($path === null, 404);

        $disk = Storage::disk('local');
        $mime = $disk->mimeType($path);
        abort_unless(in_array($mime, self::MIME_TYPES, true), 404);

        return response($disk->get($path), 200, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
