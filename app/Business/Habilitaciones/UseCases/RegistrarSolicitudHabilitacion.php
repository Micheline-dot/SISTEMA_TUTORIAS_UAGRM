<?php

namespace App\Business\Habilitaciones\UseCases;

use App\Business\Habilitaciones\Contracts\SolicitudHabilitacionRepository;
use InvalidArgumentException;

final class RegistrarSolicitudHabilitacion
{
    public function __construct(
        private SolicitudHabilitacionRepository $repository
    ) {
    }

    public function ejecutar(int $idUsuario, array $datos): int
    {
        if ($idUsuario <= 0) {
            throw new InvalidArgumentException('Usuario no válido.');
        }

        $tipo = $datos['tipo_personal'] ?? '';

        $documentos = [
            'DOCENTE' => [
                'codigo_doc',
                'curriculum',
            ],
            'AUXILIAR' => [
                'codigo_auxiliar',
                'curriculum',
                'documento_respaldo',
            ],
            'ESTUDIANTE' => [
                'carta_solicitud',
                'curriculum',
                'registro_academico',
                'historial_academico',
            ],
        ];

        if (!isset($documentos[$tipo])) {
            throw new InvalidArgumentException('Tipo de personal no válido.');
        }

        foreach ($documentos[$tipo] as $documento) {
            if (empty($datos[$documento])) {
                throw new InvalidArgumentException(
                    "El documento {$documento} es obligatorio."
                );
            }
        }

        $datos['id_usuario'] = $idUsuario;
        $datos['estado'] = 'PENDIENTE';

        return $this->repository->crear($datos);
    }
}