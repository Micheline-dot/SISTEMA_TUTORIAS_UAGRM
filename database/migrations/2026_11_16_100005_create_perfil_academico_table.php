<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabla "perfil_academico" (script 7.3.2.3): 1 a 1 con tutor. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfil_academico', function (Blueprint $table): void {
            $table->integer('id_perfil_academico')->autoIncrement()->generatedAs()->always()->primary();
            $table->text('biografia')->nullable();
            $table->text('enlaces')->nullable();
            $table->text('conocimiento')->nullable();
            $table->integer('id_tutor')->unique('perfil_academico_tutor_unique');

            $table->foreign('id_tutor', 'perfil_academico_tutor_foreign')
                ->references('id_tutor')->on('tutor')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_academico');
    }
};
