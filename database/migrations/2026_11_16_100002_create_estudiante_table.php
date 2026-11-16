<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabla "estudiante" (script 7.3.2.3). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiante', function (Blueprint $table): void {
            $table->integer('id_estudiante')->primary();
            $table->string('registro', 30)->unique('estudiante_registro_unique');

            $table->foreign('id_estudiante', 'estudiante_usuario_foreign')
                ->references('id_usuario')->on('usuario')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiante');
    }
};
