<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabla "temario" (script 7.3.2.3): PK compuesta (id_curso, id_temario). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temario', function (Blueprint $table): void {
            $table->integer('id_curso');
            $table->integer('id_temario')->generatedAs()->always();
            $table->string('nombre', 150);
            $table->date('fecha')->nullable();
            $table->string('estado', 20)->nullable();

            $table->primary(['id_curso', 'id_temario'], 'temario_primary');
            $table->foreign('id_curso', 'temario_curso_foreign')
                ->references('id_curso')->on('curso')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temario');
    }
};
