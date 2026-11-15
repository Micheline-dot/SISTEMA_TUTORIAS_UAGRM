<?php

namespace App\Business\Habilitaciones\UseCases;

use App\Business\Habilitaciones\Contracts\SolicitudHabilitacionRepository;
use App\Business\Shared\Contracts\TransactionManager;
use App\Business\Usuarios\Contracts\RoleAssignmentRepository;
use DomainException;

final class AprobarSolicitudHabilitacion
{
    public function __construct(
        private SolicitudHabilitacionRepository $solicitudes,
        private RoleAssignmentRepository $roles,
        private TransactionManager $transactions,
    ) {
    }

    public function ejecutar(
        int $idSolicitud,
        int $idAdministrador,
        ?string $observacion = null,
    ): void {
        if ($idSolicitud <= 0 || $idAdministrador <= 0) {
            throw new DomainException('Identificadores no válidos.');
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
                'APROBADA',
                $observacion
            );

            if (!$actualizada) {
                throw new DomainException(
                    'No fue posible aprobar la solicitud.'
                );
            }

            $this->roles->assign(
                (string) $solicitud['id_usuario'],
                'TUTOR'
            );
        });
    }
}
