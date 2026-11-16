<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla "solicitud_habilitacion" (script 7.3.2.3).
 * estado: pendiente, aprobada, rechazada.
 * Columnas adicionales del flujo ya implementado: tipo_academico (documentos
 * exigidos y tipo del tutor creado) y codigo (código de docente/auxiliar).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_habilitacion', function (Blueprint $table): void {
            $table->integer('id_solicitud')->autoIncrement()->generatedAs()->always()->primary();
            $table->string('estado', 20)->default('pendiente');
            $table->date('fecha')->useCurrent();
            $table->text('comentario_rechazo')->nullable();
            $table->integer('id_administrador')->nullable();
            $table->integer('id_estudiante');
            $table->string('tipo_academico', 30);
            $table->string('codigo', 50)->nullable();

            $table->foreign('id_administrador', 'solicitud_habilitacion_administrador_foreign')
                ->references('id_administrador')->on('administrador');
            $table->foreign('id_estudiante', 'solicitud_habilitacion_estudiante_foreign')
                ->references('id_estudiante')->on('estudiante');

            $table->index(['estado', 'fecha'], 'solicitud_habilitacion_estado_index');
        });

        DB::statement("
            ALTER TABLE solicitud_habilitacion ADD CONSTRAINT solicitud_habilitacion_estado_check
            CHECK (estado IN ('pendiente', 'aprobada', 'rechazada'))
        ");
        DB::statement("
            ALTER TABLE solicitud_habilitacion ADD CONSTRAINT solicitud_habilitacion_tipo_check
            CHECK (tipo_academico IN ('DOCENTE', 'AUXILIAR', 'ESTUDIANTE'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_habilitacion');
    }
};
