<?php

namespace App\Business\Notificaciones\UseCases;

use App\Business\Notificaciones\Contracts\NotificacionRepository;
use InvalidArgumentException;

/**
 * CU19: servicio que usan los demás casos de uso para avisar a uno o
 * varios usuarios (resultado de solicitudes, cancelaciones, etc.).
 */
final class EnviarNotificacion
{
    public function __construct(private readonly NotificacionRepository $notificaciones)
    {
    }

    /** @param list<string|int> $idsUsuario */
    public function aUsuarios(array $idsUsuario, string $titulo, string $mensaje, ?string $enlace = null): void
    {
        $ids = array_values(array_unique(array_filter(
            array_map(static fn ($id): string => (string) $id, $idsUsuario),
            static fn (string $id): bool => ctype_digit($id) && $id !== '0',
        )));

        if ($ids === []) {
            return;
        }

        $titulo = trim($titulo);
        $mensaje = trim($mensaje);

        if ($titulo === '' || $mensaje === '') {
            throw new InvalidArgumentException('La notificación necesita título y mensaje.');
        }

        // Solo rutas internas (empiezan con "/"), nunca URLs externas.
        if ($enlace !== null && ! str_starts_with($enlace, '/')) {
            throw new InvalidArgumentException('El enlace de la notificación debe ser una ruta interna.');
        }

        $this->notificaciones->crearParaUsuarios(
            $ids,
            mb_substr($titulo, 0, 150),
            mb_substr($mensaje, 0, 500),
            $enlace,
        );
    }

    public function aUsuario(string|int $idUsuario, string $titulo, string $mensaje, ?string $enlace = null): void
    {
        $this->aUsuarios([$idUsuario], $titulo, $mensaje, $enlace);
    }
}
