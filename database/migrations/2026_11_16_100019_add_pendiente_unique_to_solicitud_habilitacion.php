<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * CU03 (extensión al script 7.3.2.3): un estudiante solo puede tener UNA
 * solicitud de habilitación pendiente a la vez. Índice único parcial para
 * que la regla se cumpla aun con envíos simultáneos.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE UNIQUE INDEX solicitud_habilitacion_pendiente_unique
            ON solicitud_habilitacion (id_estudiante)
            WHERE estado = 'pendiente'
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS solicitud_habilitacion_pendiente_unique');
    }
};
