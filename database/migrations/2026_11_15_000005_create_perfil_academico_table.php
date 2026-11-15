<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfil_academico', function (Blueprint $table): void {
            $table->integer('id_perfil_academico')->autoIncrement()->generatedAs()->always()->primary();
            $table->integer('id_usuario');
            $table->integer('id_facultad');

            $table->unique('id_usuario', 'perfil_academico_usuario_unique');
            $table->foreign('id_usuario', 'perfil_academico_usuario_foreign')
                ->references('id_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('id_facultad', 'perfil_academico_facultad_foreign')
                ->references('id_facultad')->on('facultades')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_academico');
    }
};
