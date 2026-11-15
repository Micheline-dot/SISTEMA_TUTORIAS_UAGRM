<?php

namespace App\Business\Habilitaciones\UseCases;

use App\Business\Habilitaciones\Contracts\SolicitudHabilitacionRepository;
use App\Business\Shared\Contracts\TransactionManager;
use DomainException;

final class RechazarSolicitudHabilitacion
{
    public function __construct(
        private SolicitudHabilitacionRepository $solicitudes,
        private TransactionManager $transactions,
    ) {
    }

    public function ejecutar(
        int $idSolicitud,
        int $idAdministrador,
        string $observacion,
    ): void {
        if ($idSolicitud <= 0 || $idAdministrador <= 0) {
            throw new DomainException('Los identificadores no son válidos.');
        }

        $observacion = trim($observacion);

        if ($observacion === '') {
            throw new DomainException(
                'Debes indicar el motivo del rechazo.'
            );
        }

        if (mb_strlen($observacion) > 2000) {
            throw new DomainException(
                'El motivo no puede superar los 2000 caracteres.'
            );
        }

        $this->transactions->run(function () use (
            $idSolicitud,
            $idAdministrador,
            $observacion
        ): void {
            $solicitud = $this->solicitudes->buscarPorId($idSolicitud);

            if ($solicitud === null) {
                throw new DomainException('La solicitud no existe.');
            }

            if ($solicitud['estado'] !== 'PENDIENTE') {
                throw new DomainException(
                    'La solicitud ya fue revisada anteriormente.'
                );
            }

            $actualizada = $this->solicitudes->actualizarRevision(
                $idSolicitud,
                $idAdministrador,
                'RECHAZADA',
                $observacion
            );

            if (!$actualizada) {
                throw new DomainException(
                    'No fue posible rechazar la solicitud.'
                );
            }
        });
    }
}
