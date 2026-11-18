<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * (+) Configuración general del sistema administrable (clave → valor).
 * Primer uso: "logo" = ruta del logo institucional en el disco privado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table): void {
            $table->string('clave', 60)->primary();
            $table->string('valor', 500)->nullable();
            $table->timestamp('actualizado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
