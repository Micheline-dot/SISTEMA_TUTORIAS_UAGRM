<?php

namespace App\Business\Notificaciones\DTO;

final readonly class Notificacion
{
    public function __construct(
        public string $id,
        public string $titulo,
        public string $mensaje,
        public ?string $enlace,
        public string $fecha,
        public bool $leida,
    ) {
    }
}
