<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabla "administrador" (script 7.3.2.3). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrador', function (Blueprint $table): void {
            $table->integer('id_administrador')->primary();
            $table->string('cargo', 100);

            $table->foreign('id_administrador', 'administrador_usuario_foreign')
                ->references('id_usuario')->on('usuario')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrador');
    }
};
