<?php

namespace App\Business\Configuracion\UseCases;

/**
 * (+) Foto de fondo de las pantallas de inicio de sesión y registro.
 * Sin foto se usa el fondo claro por defecto.
 */
final class ManageLoginBackground extends ManageSystemImage
{
    public const KEY = 'fondo_login';

    /** El límite real lo pone también upload_max_filesize de PHP. */
    public const MAX_KILOBYTES = 5120;

    protected function key(): string
    {
        return self::KEY;
    }

    protected function label(): string
    {
        return 'el fondo';
    }
}
