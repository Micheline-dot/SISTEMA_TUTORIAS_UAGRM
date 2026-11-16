<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla "curso" (script 7.3.2.3).
 *
 * Diferencias justificadas con el script:
 * - titulo, modalidad, cupos, es_gratuito e id_categoria son anulables porque el
 *   curso se crea como BORRADOR (CU07); se exigen al publicar (CU09).
 * - Columnas adicionales del flujo ya implementado: descripcion, fecha_inicio,
 *   fecha_finalizacion, estado, motivo_cancelacion, fecha_cancelacion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso', function (Blueprint $table): void {
            $table->integer('id_curso')->autoIncrement()->generatedAs()->always()->primary();
            $table->string('titulo', 150)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('modalidad', 20)->nullable();
            $table->integer('cupos')->nullable();
            $table->boolean('es_gratuito')->nullable()->default(true);
            $table->integer('id_categoria')->nullable();
            $table->integer('id_tutor');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_finalizacion')->nullable();
            $table->string('estado', 20)->default('BORRADOR');
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamp('fecha_cancelacion')->nullable();

            $table->foreign('id_categoria', 'curso_categoria_foreign')
                ->references('id_categoria')->on('categoria');
            $table->foreign('id_tutor', 'curso_tutor_foreign')
                ->references('id_tutor')->on('tutor');

            $table->index('id_tutor', 'curso_tutor_index');
            $table->index('estado', 'curso_estado_index');
        });

        DB::statement("
            ALTER TABLE curso ADD CONSTRAINT curso_modalidad_check
            CHECK (modalidad IS NULL OR modalidad IN ('PRESENCIAL', 'VIRTUAL', 'HIBRIDA'))
        ");
        DB::statement("
            ALTER TABLE curso ADD CONSTRAINT curso_estado_check
            CHECK (estado IN ('BORRADOR', 'PUBLICADO', 'CANCELADO', 'FINALIZADO'))
        ");
        DB::statement('ALTER TABLE curso ADD CONSTRAINT curso_cupos_check CHECK (cupos IS NULL OR cupos > 0)');
        DB::statement("
            ALTER TABLE curso ADD CONSTRAINT curso_fechas_check
            CHECK (fecha_inicio IS NULL OR fecha_finalizacion IS NULL OR fecha_finalizacion >= fecha_inicio)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('curso');
    }
};
