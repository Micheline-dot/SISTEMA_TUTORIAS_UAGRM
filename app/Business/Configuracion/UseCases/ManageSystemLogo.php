<?php

namespace App\Business\Configuracion\UseCases;

/**
 * (+) Logo institucional: se muestra en el login, el registro y el menú lateral.
 * Sin logo se usa el nombre del sistema en texto.
 */
final class ManageSystemLogo extends ManageSystemImage
{
    public const KEY = 'logo';

    public const MAX_KILOBYTES = 2048;

    protected function key(): string
    {
        return self::KEY;
    }

    protected function label(): string
    {
        return 'el logo';
    }

    public function hasLogo(): bool
    {
        return $this->exists();
    }
}
