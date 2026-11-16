<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabla "documento_solicitud" (script 7.3.2.3). nombre = tipo de documento; ruta = PDF protegido. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_solicitud', function (Blueprint $table): void {
            $table->integer('id_documento')->autoIncrement()->generatedAs()->always()->primary();
            $table->string('nombre', 150);
            $table->string('ruta', 500)->nullable();
            $table->integer('id_solicitud');

            $table->foreign('id_solicitud', 'documento_solicitud_solicitud_foreign')
                ->references('id_solicitud')->on('solicitud_habilitacion')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_solicitud');
    }
};
