<?php

namespace App\DataAccess\PostgreSQL\Configuracion\Repositories;

use App\Business\Configuracion\Contracts\SystemSettingsRepository;
use Illuminate\Support\Facades\DB;

final class DbSystemSettingsRepository implements SystemSettingsRepository
{
    /** @var array<string, ?string> caché por petición (el logo se consulta en cada página) */
    private array $cache = [];

    public function get(string $key): ?string
    {
        if (! array_key_exists($key, $this->cache)) {
            $this->cache[$key] = DB::table('configuracion')->where('clave', $key)->value('valor');
        }

        return $this->cache[$key];
    }

    public function set(string $key, ?string $value): void
    {
        DB::table('configuracion')->upsert(
            [['clave' => $key, 'valor' => $value, 'actualizado_en' => now()]],
            ['clave'],
            ['valor', 'actualizado_en'],
        );
        $this->cache[$key] = $value;
    }
}
