<?php

namespace App\Business\Notificaciones\Contracts;

use App\Business\Notificaciones\DTO\Notificacion;

interface NotificacionRepository
{
    /**
     * Crea una notificación por cada destinatario (tablas notificacion +
     * notificacion_usuario).
     *
     * @param list<string> $idsUsuario
     */
    public function crearParaUsuarios(array $idsUsuario, string $titulo, string $mensaje, ?string $enlace): void;

    /** @return list<Notificacion> la más reciente primero */
    public function listarDeUsuario(string $idUsuario, bool $soloNoLeidas, int $limite): array;

    public function contarNoLeidas(string $idUsuario): int;

    /** Devuelve false si la notificación no pertenece al usuario. */
    public function marcarLeida(string $idUsuario, string $idNotificacion): bool;

    public function marcarTodasLeidas(string $idUsuario): int;

    /** Ruta interna a la que lleva la notificación, si pertenece al usuario. */
    public function enlaceDe(string $idUsuario, string $idNotificacion): ?string;
}
