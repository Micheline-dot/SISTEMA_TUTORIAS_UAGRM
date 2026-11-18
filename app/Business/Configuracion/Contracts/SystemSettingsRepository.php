<?php

namespace App\Business\Configuracion\Contracts;

interface SystemSettingsRepository
{
    public function get(string $key): ?string;

    public function set(string $key, ?string $value): void;
}
