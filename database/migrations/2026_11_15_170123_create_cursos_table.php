<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table): void {
            $table->integer('id_curso')->primary();
            $table->integer('id_tutor');

            $table->string('titulo')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('modalidad')->nullable();
            $table->integer('cupos')->nullable();
            $table->boolean('es_gratuito')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_finalizacion')->nullable();
            $table->string('estado')->default('BORRADOR');

            $table->foreign('id_tutor')
                ->references('id_usuario')
                ->on('usuarios')
                ->restrictOnDelete();
        });

        DB::statement(
            'ALTER TABLE cursos ALTER COLUMN id_curso ADD GENERATED ALWAYS AS IDENTITY'
        );

        DB::statement("
            ALTER TABLE cursos ADD CONSTRAINT cursos_modalidad_check
            CHECK (modalidad IS NULL OR modalidad IN ('PRESENCIAL', 'VIRTUAL', 'HIBRIDA'))
        ");

        DB::statement("
            ALTER TABLE cursos ADD CONSTRAINT cursos_estado_check
            CHECK (estado IN ('BORRADOR', 'PUBLICADO', 'CANCELADO', 'FINALIZADO'))
        ");

        DB::statement("
            ALTER TABLE cursos ADD CONSTRAINT cursos_cupos_check
            CHECK (cupos IS NULL OR cupos > 0)
        ");

        DB::statement("
            ALTER TABLE cursos ADD CONSTRAINT cursos_fechas_check
            CHECK (
                fecha_inicio IS NULL
                OR fecha_finalizacion IS NULL
                OR fecha_finalizacion >= fecha_inicio
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};