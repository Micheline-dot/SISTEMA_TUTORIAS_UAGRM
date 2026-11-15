<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table): void {
            $table->integer('id_usuario')->autoIncrement()->generatedAs()->always()->primary();
            $table->text('nombre_completo');
            $table->text('correo');
            $table->text('contrasena_hash');
            $table->boolean('activo')->default(true);
            $table->text('telefono');

            $table->unique('correo', 'usuarios_correo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
