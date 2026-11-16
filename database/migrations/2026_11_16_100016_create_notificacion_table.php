<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabla "notificacion" (script 7.3.2.3). estado: false = no leída. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacion', function (Blueprint $table): void {
            $table->integer('id_notificacion')->autoIncrement()->generatedAs()->always()->primary();
            $table->string('mensaje', 500);
            $table->date('fecha')->useCurrent();
            $table->boolean('estado')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion');
    }
};
