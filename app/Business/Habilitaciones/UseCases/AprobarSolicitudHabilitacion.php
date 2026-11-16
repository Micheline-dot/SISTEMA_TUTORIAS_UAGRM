<?php

namespace App\Business\Habilitaciones\UseCases;

use App\Business\Habilitaciones\Contracts\SolicitudHabilitacionRepository;
use App\Business\Shared\Contracts\TransactionManager;
use App\Business\Usuarios\Contracts\AcademicProfileRepository;
use App\Business\Usuarios\Contracts\RoleAssignmentRepository;
use DomainException;

/**
 * Aprueba la solicitud: el usuario pasa a ser también Tutor (conserva su
 * rol de Estudiante) con el tipo académico declarado, y se le crea su
 * PerfilAcademico vacío para completarlo después.
 */
final class AprobarSolicitudHabilitacion
{
    public function __construct(
        private SolicitudHabilitacionRepository $solicitudes,
        private RoleAssignmentRepository $roles,
        private AcademicProfileRepository $perfiles,
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

            $idUsuario = (string) $solicitud['id_usuario'];

            $this->roles->assignTutor($idUsuario, (string) $solicitud['tipo_personal']);
            $this->perfiles->ensureForTutor($idUsuario);
        });
    }
}
