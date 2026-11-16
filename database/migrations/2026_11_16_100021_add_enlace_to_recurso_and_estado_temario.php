<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CU08 Gestionar temarios (extensión al script 7.3.2.3):
 *  - recurso.enlace: dirección del material (el sistema guarda enlaces, no archivos).
 *  - temario.estado: PENDIENTE / COMPLETADO (avance del tema).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurso', function (Blueprint $table): void {
            $table->string('enlace', 255)->nullable();
        });

        DB::statement("UPDATE temario SET estado = 'PENDIENTE' WHERE estado IS NULL OR estado NOT IN ('PENDIENTE', 'COMPLETADO')");
        DB::statement("ALTER TABLE temario ALTER COLUMN estado SET DEFAULT 'PENDIENTE'");
        DB::statement("
            ALTER TABLE temario ADD CONSTRAINT temario_estado_check
            CHECK (estado IN ('PENDIENTE', 'COMPLETADO'))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE temario DROP CONSTRAINT IF EXISTS temario_estado_check');
        DB::statement('ALTER TABLE temario ALTER COLUMN estado DROP DEFAULT');

        Schema::table('recurso', function (Blueprint $table): void {
            $table->dropColumn('enlace');
        });
    }
};
