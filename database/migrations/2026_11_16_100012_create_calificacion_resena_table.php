<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla "calificacion_resena" (script 7.3.2.3).
 * Una calificación por inscripción-curso; solo para participación verificada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificacion_resena', function (Blueprint $table): void {
            $table->integer('id_calificacion')->autoIncrement()->generatedAs()->always()->primary();
            $table->integer('puntuacion');
            $table->date('fecha')->useCurrent();
            $table->text('comentario')->nullable();
            $table->integer('id_inscripcion');
            $table->integer('id_curso');

            $table->unique(['id_inscripcion', 'id_curso'], 'calificacion_resena_inscripcion_unique');
            $table->foreign(['id_inscripcion', 'id_curso'], 'calificacion_resena_curso_inscripcion_foreign')
                ->references(['id_inscripcion', 'id_curso'])->on('curso_inscripcion');
        });

        DB::statement('
            ALTER TABLE calificacion_resena ADD CONSTRAINT calificacion_resena_puntuacion_check
            CHECK (puntuacion BETWEEN 1 AND 5)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('calificacion_resena');
    }
};
