<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Tabla "tutor" (script 7.3.2.3). Se crea al aprobar una solicitud de habilitación. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutor', function (Blueprint $table): void {
            $table->integer('id_tutor')->primary();
            $table->string('tipo_academico', 30);

            $table->foreign('id_tutor', 'tutor_usuario_foreign')
                ->references('id_usuario')->on('usuario')->cascadeOnDelete();
        });

        DB::statement("
            ALTER TABLE tutor ADD CONSTRAINT tutor_tipo_academico_check
            CHECK (tipo_academico IN ('DOCENTE', 'AUXILIAR', 'ESTUDIANTE'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor');
    }
};
