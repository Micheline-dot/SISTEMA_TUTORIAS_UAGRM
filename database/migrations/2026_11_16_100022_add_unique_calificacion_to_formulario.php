<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CU17 (extensión al script 7.3.2.3): cada calificación tiene a lo sumo un
 * formulario de satisfacción. formulario.preguntas guarda preguntas y
 * respuestas en JSON.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formulario', function (Blueprint $table): void {
            $table->unique('id_calificacion', 'formulario_calificacion_unique');
        });
    }

    public function down(): void
    {
        Schema::table('formulario', function (Blueprint $table): void {
            $table->dropUnique('formulario_calificacion_unique');
        });
    }
};
