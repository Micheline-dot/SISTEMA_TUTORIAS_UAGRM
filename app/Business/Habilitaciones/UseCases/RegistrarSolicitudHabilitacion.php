<?php

namespace App\Business\Habilitaciones\UseCases;

use App\Business\Habilitaciones\Contracts\SolicitudHabilitacionRepository;
use App\Business\Habilitaciones\Exceptions\DatosAcademicosIncompletos;
use App\Business\Habilitaciones\Exceptions\SolicitudPendienteExistente;
use App\Business\Habilitaciones\Exceptions\SolicitudYaAprobada;
use App\Business\Notificaciones\UseCases\EnviarNotificacion;
use App\Business\Usuarios\Contracts\RoleAssignmentRepository;
use App\Business\Usuarios\Contracts\UserProfileRepository;
use InvalidArgumentException;

/**
 * CU03 Solicitar habilitación como tutor.
 *
 * Requisitos: haber registrado los datos académicos en "Mi perfil" (CU02),
 * no tener otra solicitud pendiente ni una ya aprobada.
 * El tipo de personal y el código (docente / auxiliar) se toman del perfil,
 * de modo que la solicitud siempre coincide con lo registrado.
 */
final class RegistrarSolicitudHabilitacion
{
    private const DOCUMENTOS = [
        'DOCENTE' => ['curriculum'],
        'AUXILIAR' => ['curriculum', 'documento_respaldo'],
        'ESTUDIANTE' => ['carta_solicitud', 'curriculum', 'registro_academico', 'historial_academico'],
    ];

    public function __construct(
        private SolicitudHabilitacionRepository $repository,
        private UserProfileRepository $perfiles,
        private RoleAssignmentRepository $roles,
        private EnviarNotificacion $notificar,
    ) {
    }

    public function ejecutar(int $idUsuario, array $datos): int
    {
        if ($idUsuario <= 0) {
            throw new InvalidArgumentException('Usuario no válido.');
        }

        $perfil = $this->perfiles->find((string) $idUsuario);

        if ($perfil === null || ! $perfil->academicDataCompleted()) {
            throw new DatosAcademicosIncompletos(
                'Antes de solicitar ser tutor debes registrar tus datos académicos en "Mi perfil".'
            );
        }

        $previas = $this->repository->buscarPorUsuario($idUsuario);

        if (ConsultarMisSolicitudesHabilitacion::tieneEstado($previas, 'PENDIENTE')) {
            throw new SolicitudPendienteExistente();
        }

        // Ya es tutor habilitado. (Si un admin lo degradó, puede volver a solicitar.)
        if ($this->roles->hasRole((string) $idUsuario, 'TUTOR')) {
            throw new SolicitudYaAprobada();
        }

        $tipo = $perfil->registrationType;

        if (! isset(self::DOCUMENTOS[$tipo])) {
            throw new InvalidArgumentException('Tipo de personal no válido.');
        }

        if (isset($datos['tipo_personal']) && $datos['tipo_personal'] !== $tipo) {
            throw new InvalidArgumentException('El tipo de personal no coincide con el registrado en tu perfil.');
        }

        foreach (self::DOCUMENTOS[$tipo] as $documento) {
            if (empty($datos[$documento])) {
                throw new InvalidArgumentException(
                    "El documento {$documento} es obligatorio."
                );
            }
        }

        $datos['tipo_personal'] = $tipo;
        $datos['codigo_doc'] = $tipo === 'DOCENTE' ? $perfil->teacherCode : null;
        $datos['codigo_auxiliar'] = $tipo === 'AUXILIAR' ? $perfil->assistantRegistry : null;
        $datos['id_usuario'] = $idUsuario;
        $datos['estado'] = 'PENDIENTE';

        $idSolicitud = $this->repository->crear($datos);

        // CU19: avisar a los administradores que hay una solicitud por revisar.
        $this->notificar->aUsuarios(
            $this->roles->activeUserIdsWithRole('ADMINISTRADOR'),
            'Nueva solicitud de tutor',
            "{$perfil->firstNames} {$perfil->lastNames} solicitó habilitarse como tutor (".mb_strtolower($tipo).').',
            '/administracion/solicitudes-tutor',
        );

        return $idSolicitud;
    }
}
