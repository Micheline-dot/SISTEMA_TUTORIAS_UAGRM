<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabla "formulario" (script 7.3.2.3): composición con calificacion_resena. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formulario', function (Blueprint $table): void {
            $table->integer('id_formulario')->autoIncrement()->generatedAs()->always()->primary();
            $table->string('titulo', 150);
            $table->text('preguntas')->nullable();
            $table->integer('id_calificacion');

            $table->foreign('id_calificacion', 'formulario_calificacion_foreign')
                ->references('id_calificacion')->on('calificacion_resena')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formulario');
    }
};
