<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * (+) Imagen de portada opcional de la tutoría (ruta en el disco privado).
 * Migración aditiva: no modifica datos existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curso', function (Blueprint $table): void {
            $table->string('portada', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('curso', function (Blueprint $table): void {
            $table->dropColumn('portada');
        });
    }
};
