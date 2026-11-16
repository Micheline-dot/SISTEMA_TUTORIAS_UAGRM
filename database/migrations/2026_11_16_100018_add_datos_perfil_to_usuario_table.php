<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CU02 Gestionar perfil de usuario (extensión al script 7.3.2.3).
 *
 * - El registro público solo pide nombres, apellidos, correo y contraseña;
 *   teléfono, facultad y registro universitario se completan en "Mi perfil".
 * - estudiante.registro pasa a ser opcional (UNIQUE admite varios NULL).
 * - usuario.foto_perfil: ruta del archivo en el disco privado.
 * - usuario.registro_auxiliar / codigo_docente: según tipo_registro.
 * - Los datos académicos (facultad + tipo + registros) se registran una sola
 *   vez: facultad NOT NULL indica que ya fueron completados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuario', function (Blueprint $table): void {
            $table->string('foto_perfil', 255)->nullable();
            $table->string('registro_auxiliar', 30)->nullable()->unique('usuario_registro_auxiliar_unique');
            $table->string('codigo_docente', 30)->nullable()->unique('usuario_codigo_docente_unique');
        });

        Schema::table('estudiante', function (Blueprint $table): void {
            $table->string('registro', 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table): void {
            $table->dropUnique('usuario_registro_auxiliar_unique');
            $table->dropUnique('usuario_codigo_docente_unique');
            $table->dropColumn(['foto_perfil', 'registro_auxiliar', 'codigo_docente']);
        });
    }
};
