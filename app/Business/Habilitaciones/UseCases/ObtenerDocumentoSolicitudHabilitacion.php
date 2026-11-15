<?php

namespace App\Business\Habilitaciones\UseCases;

use App\Business\Habilitaciones\Contracts\SolicitudHabilitacionRepository;
use DomainException;

final class ObtenerDocumentoSolicitudHabilitacion
{
    private const DOCUMENTOS_PERMITIDOS = [
        'curriculum',
        'documento_respaldo',
        'carta_solicitud',
        'registro_academico',
        'historial_academico',
    ];

    public function __construct(
        private SolicitudHabilitacionRepository $solicitudes,
    ) {
    }

    public function ejecutar(
        int $idSolicitud,
        string $tipoDocumento
    ): string {
        if ($idSolicitud <= 0) {
            throw new DomainException(
                'El identificador de la solicitud no es válido.'
            );
        }

        if (!in_array($tipoDocumento, self::DOCUMENTOS_PERMITIDOS, true)) {
            throw new DomainException(
                'El tipo de documento solicitado no está permitido.'
            );
        }

        $solicitud = $this->solicitudes->buscarPorId($idSolicitud);

        if ($solicitud === null) {
            throw new DomainException('La solicitud no existe.');
        }

        $rutaDocumento = $solicitud[$tipoDocumento] ?? null;

        if (!is_string($rutaDocumento) || trim($rutaDocumento) === '') {
            throw new DomainException(
                'El documento solicitado no está registrado.'
            );
        }

        return $rutaDocumento;
    }
}
