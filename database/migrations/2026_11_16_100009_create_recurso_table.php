<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabla "recurso" (script 7.3.2.3): PK (id_recurso, id_temario, id_curso). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurso', function (Blueprint $table): void {
            $table->integer('id_recurso')->generatedAs()->always();
            $table->integer('id_temario');
            $table->integer('id_curso');
            $table->string('nombre', 150);

            $table->primary(['id_recurso', 'id_temario', 'id_curso'], 'recurso_primary');
            $table->foreign(['id_curso', 'id_temario'], 'recurso_temario_foreign')
                ->references(['id_curso', 'id_temario'])->on('temario')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurso');
    }
};
