<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->integer('id_rol')->autoIncrement()->generatedAs()->always()->primary();
            $table->text('nombre');

            $table->unique('nombre', 'roles_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
