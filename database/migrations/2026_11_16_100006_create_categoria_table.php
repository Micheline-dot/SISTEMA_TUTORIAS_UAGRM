<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clase "Categoria".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria', function (Blueprint $table): void {
            $table->integer('id_categoria')->autoIncrement()->generatedAs()->always()->primary();
            $table->string('nombre', 100)->unique('categoria_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria');
    }
};
