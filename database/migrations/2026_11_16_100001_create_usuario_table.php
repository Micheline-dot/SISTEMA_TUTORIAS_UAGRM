<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla "usuario" (script 7.3.2.3).
 * tipo_registro: categoría académica referencial (ESTUDIANTE, DOCENTE, AUXILIAR),
 * se actualiza desde "Mi perfil"; ADMINISTRADOR solo para cuentas de administración.
 * Columna adicional: activo (desactivar cuentas conservando el historial).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table): void {
            $table->integer('id_usuario')->autoIncrement()->generatedAs()->always()->primary();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('correo', 150)->unique('usuario_correo_unique');
            $table->string('contrasena', 255);
            $table->string('telefono', 20)->nullable();
            $table->string('facultad', 100)->nullable();
            $table->string('tipo_registro', 30)->default('ESTUDIANTE');
            $table->boolean('activo')->default(true);
        });

        DB::statement("
            ALTER TABLE usuario ADD CONSTRAINT usuario_tipo_registro_check
            CHECK (tipo_registro IN ('ESTUDIANTE', 'DOCENTE', 'AUXILIAR', 'ADMINISTRADOR'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
