<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_rol', function (Blueprint $table): void {
            $table->integer('id_usuario');
            $table->integer('id_rol');

            $table->primary(['id_usuario', 'id_rol'], 'usuario_rol_primary');
            $table->foreign('id_usuario', 'usuario_rol_usuario_foreign')
                ->references('id_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('id_rol', 'usuario_rol_rol_foreign')
                ->references('id_rol')->on('roles')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_rol');
    }
};
