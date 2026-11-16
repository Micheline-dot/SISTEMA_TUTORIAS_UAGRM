<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extensiones al script 7.3.2.3:
 *  - CU19: notificacion.titulo y notificacion.enlace (a qué pantalla lleva).
 *    Se crea una notificacion por destinatario; notificacion.estado = leída.
 *  - CU20: tutor.habilitado. Degradar a un tutor no borra la fila (sus cursos
 *    la referencian): se marca habilitado = false y pierde el rol TUTOR.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificacion', function (Blueprint $table): void {
            $table->string('titulo', 150)->nullable();
            $table->string('enlace', 255)->nullable();
        });

        Schema::table('tutor', function (Blueprint $table): void {
            $table->boolean('habilitado')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('notificacion', function (Blueprint $table): void {
            $table->dropColumn(['titulo', 'enlace']);
        });

        Schema::table('tutor', function (Blueprint $table): void {
            $table->dropColumn('habilitado');
        });
    }
};
