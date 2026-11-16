<?php

namespace App\Presentation\Http\Controllers;

use App\Business\Habilitaciones\UseCases\ObtenerDocumentoSolicitudHabilitacion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use DomainException;

final class DocumentoSolicitudHabilitacionController
{
    public function __invoke(
        int $idSolicitud,
        string $tipoDocumento,
        ObtenerDocumentoSolicitudHabilitacion $casoUso
    ): Response {
        abort_unless(auth()->check(), 401);

        $esAdministrador = auth()->user()->tieneRol('ADMINISTRADOR');

        abort_unless($esAdministrador, 403);

        try {
            $ruta = $casoUso->ejecutar(
                $idSolicitud,
                $tipoDocumento
            );
        } catch (DomainException $exception) {
            abort(404);
        }

        $disco = Storage::disk('local');

        abort_unless($disco->exists($ruta), 404);

        $contenido = $disco->get($ruta);

        $mime = $disco->mimeType($ruta);

        abort_unless($mime === 'application/pdf', 404);

        return response($contenido, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="documento.pdf"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
        ]);
    }
}
