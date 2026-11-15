
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_habilitacion_tutor', function (Blueprint $table) {
            $table->integer('id_solicitud')->primary();

            $table->integer('id_usuario');

            $table->string('tipo_personal', 20);

            // Documentación de docentes
            $table->string('codigo_doc', 50)->nullable();

            // Documentación de auxiliares
            $table->string('codigo_auxiliar', 50)->nullable();
            $table->string('documento_respaldo')->nullable();

            // Documentación de estudiantes
            $table->string('carta_solicitud')->nullable();
            $table->string('registro_academico')->nullable();
            $table->string('historial_academico')->nullable();

            // Documento común
            $table->string('curriculum')->nullable();

            // Validación administrativa
            $table->string('estado', 20)->default('PENDIENTE');
            $table->integer('id_administrador_revisor')->nullable();
            $table->text('observacion')->nullable();
            $table->timestamp('fecha_revision')->nullable();

            $table->timestamps();

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->restrictOnDelete();

            $table->foreign('id_administrador_revisor')
                ->references('id_usuario')
                ->on('usuarios')
                ->restrictOnDelete();
        });

        DB::statement(
            'ALTER TABLE solicitudes_habilitacion_tutor
             ALTER COLUMN id_solicitud ADD GENERATED ALWAYS AS IDENTITY'
        );

        DB::statement("
            ALTER TABLE solicitudes_habilitacion_tutor
            ADD CONSTRAINT solicitud_tipo_personal_check
            CHECK (tipo_personal IN ('DOCENTE', 'AUXILIAR', 'ESTUDIANTE'))
        ");

        DB::statement("
            ALTER TABLE solicitudes_habilitacion_tutor
            ADD CONSTRAINT solicitud_estado_check
            CHECK (estado IN ('PENDIENTE', 'APROBADA', 'RECHAZADA'))
        ");

        DB::statement("
            ALTER TABLE solicitudes_habilitacion_tutor
            ADD CONSTRAINT solicitud_documentos_check
            CHECK (
                (
                    tipo_personal = 'DOCENTE'
                    AND codigo_doc IS NOT NULL
                    AND curriculum IS NOT NULL
                )
                OR
                (
                    tipo_personal = 'AUXILIAR'
                    AND codigo_auxiliar IS NOT NULL
                    AND curriculum IS NOT NULL
                    AND documento_respaldo IS NOT NULL
                )
                OR
                (
                    tipo_personal = 'ESTUDIANTE'
                    AND carta_solicitud IS NOT NULL
                    AND curriculum IS NOT NULL
                    AND registro_academico IS NOT NULL
                    AND historial_academico IS NOT NULL
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_habilitacion_tutor');
    }
};
