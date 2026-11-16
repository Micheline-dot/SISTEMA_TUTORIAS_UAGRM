<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table): void {
            $table->integer('id_inscripcion')->autoIncrement()->generatedAs()->always()->primary();
            $table->integer('id_curso');
            $table->integer('id_estudiante');
            $table->string('estado')->default('INSCRITO');
            $table->boolean('asistencia_verificada')->default(false);
            $table->timestamp('fecha_inscripcion')->useCurrent();
            $table->timestamp('fecha_cancelacion')->nullable();

            $table->foreign('id_curso')
                ->references('id_curso')
                ->on('cursos')
                ->restrictOnDelete();

            $table->foreign('id_estudiante')
                ->references('id_usuario')
                ->on('usuarios')
                ->restrictOnDelete();
        });

        DB::statement("
            ALTER TABLE inscripciones
            ADD CONSTRAINT inscripciones_estado_check
            CHECK (estado IN ('INSCRITO', 'CANCELADO'))
        ");

        DB::statement("
            CREATE UNIQUE INDEX inscripciones_activas_unique
            ON inscripciones (id_curso, id_estudiante)
            WHERE estado = 'INSCRITO'
        ");

        DB::statement("
            CREATE INDEX inscripciones_curso_estado_index
            ON inscripciones (id_curso, estado)
        ");

        DB::statement("
            CREATE INDEX inscripciones_estudiante_index
            ON inscripciones (id_estudiante)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};