<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla "curso_inscripcion" (script 7.3.2.3).
 * estado:
 *   activa     = inscrito, asistencia pendiente
 *   asistio    = el tutor registró la asistencia
 *   confirmada = el estudiante confirmó su participación (doble confirmación)
 *   cancelada  = cancelada por el estudiante o por cancelación del curso (CU14)
 *   eliminada  = retirado por el tutor (CU15)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_inscripcion', function (Blueprint $table): void {
            $table->integer('id_inscripcion');
            $table->integer('id_curso');
            $table->string('estado', 20)->default('activa');

            $table->primary(['id_inscripcion', 'id_curso'], 'curso_inscripcion_primary');
            $table->foreign('id_inscripcion', 'curso_inscripcion_inscripcion_foreign')
                ->references('id_inscripcion')->on('inscripcion')->cascadeOnDelete();
            $table->foreign('id_curso', 'curso_inscripcion_curso_foreign')
                ->references('id_curso')->on('curso');

            $table->index('id_curso', 'curso_inscripcion_curso_index');
        });

        DB::statement("
            ALTER TABLE curso_inscripcion ADD CONSTRAINT curso_inscripcion_estado_check
            CHECK (estado IN ('activa', 'asistio', 'confirmada', 'cancelada', 'eliminada'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_inscripcion');
    }
};
