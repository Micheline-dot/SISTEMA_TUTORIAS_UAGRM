<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabla "inscripcion" (script 7.3.2.3). estado: true = activa, false = cancelada. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion', function (Blueprint $table): void {
            $table->integer('id_inscripcion')->autoIncrement()->generatedAs()->always()->primary();
            $table->date('fecha')->useCurrent();
            $table->boolean('estado')->default(true);
            $table->string('motivo_cancelacion', 500)->nullable();
            $table->integer('id_usuario');

            $table->foreign('id_usuario', 'inscripcion_usuario_foreign')
                ->references('id_usuario')->on('usuario');

            $table->index('id_usuario', 'inscripcion_usuario_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion');
    }
};
