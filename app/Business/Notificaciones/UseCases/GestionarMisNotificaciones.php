<?php

namespace App\Business\Notificaciones\UseCases;

use App\Business\Notificaciones\Contracts\NotificacionRepository;
use App\Business\Notificaciones\DTO\Notificacion;

/**
 * CU19 Recibir notificaciones: bandeja del usuario, contador de no leídas,
 * marcar una o todas como leídas. Cada usuario solo accede a las suyas.
 */
final class GestionarMisNotificaciones
{
    public const LIMITE = 100;

    public function __construct(private readonly NotificacionRepository $notificaciones)
    {
    }

    /** @return list<Notificacion> */
    public function listar(string $idUsuario, bool $soloNoLeidas = false): array
    {
        return $this->notificaciones->listarDeUsuario($idUsuario, $soloNoLeidas, self::LIMITE);
    }

    public function contarNoLeidas(string $idUsuario): int
    {
        return $this->notificaciones->contarNoLeidas($idUsuario);
    }

    public function marcarLeida(string $idUsuario, string $idNotificacion): bool
    {
        return ctype_digit($idNotificacion)
            && $this->notificaciones->marcarLeida($idUsuario, $idNotificacion);
    }

    public function marcarTodasLeidas(string $idUsuario): int
    {
        return $this->notificaciones->marcarTodasLeidas($idUsuario);
    }

    /** Marca como leída y devuelve la ruta interna a la que lleva (o null). */
    public function abrir(string $idUsuario, string $idNotificacion): ?string
    {
        if (! $this->marcarLeida($idUsuario, $idNotificacion)) {
            return null;
        }

        return $this->notificaciones->enlaceDe($idUsuario, $idNotificacion);
    }
}
