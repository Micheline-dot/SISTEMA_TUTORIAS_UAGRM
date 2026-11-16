<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabla "notificacion_usuario" (script 7.3.2.3). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacion_usuario', function (Blueprint $table): void {
            $table->integer('id_usuario');
            $table->integer('id_notificacion');

            $table->primary(['id_usuario', 'id_notificacion'], 'notificacion_usuario_primary');
            $table->foreign('id_usuario', 'notificacion_usuario_usuario_foreign')
                ->references('id_usuario')->on('usuario')->cascadeOnDelete();
            $table->foreign('id_notificacion', 'notificacion_usuario_notificacion_foreign')
                ->references('id_notificacion')->on('notificacion')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_usuario');
    }
};
