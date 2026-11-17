<?php

namespace Tests\Integration\Flows;

use App\Business\Habilitaciones\Exceptions\DatosAcademicosIncompletos;
use App\Business\Habilitaciones\Exceptions\SolicitudPendienteExistente;
use App\Business\Habilitaciones\Exceptions\SolicitudYaAprobada;
use App\Business\Habilitaciones\UseCases\AprobarSolicitudHabilitacion;
use App\Business\Habilitaciones\UseCases\ConsultarMisSolicitudesHabilitacion;
use App\Business\Habilitaciones\UseCases\RechazarSolicitudHabilitacion;
use App\Business\Habilitaciones\UseCases\RegistrarSolicitudHabilitacion;
use App\Business\Notificaciones\UseCases\GestionarMisNotificaciones;
use App\Business\Usuarios\UseCases\ManageUsers;

/** CU03 Solicitar habilitación · CU04/CU05 Revisar solicitud · CU20 retirar rol. */
final class HabilitacionTutorFlowTest extends FlowTestCase
{
    public function test_exige_datos_academicos_antes_de_solicitar(): void
    {
        $this->expectException(DatosAcademicosIncompletos::class);
        app(RegistrarSolicitudHabilitacion::class)->ejecutar((int) $this->register(), ['curriculum' => 'cv.pdf']);
    }

    public function test_solicitud_pendiente_bloquea_otra_y_notifica_al_admin(): void
    {
        $student = $this->student();
        $notifications = app(GestionarMisNotificaciones::class);
        $before = $notifications->contarNoLeidas($this->adminId);

        $request = app(RegistrarSolicitudHabilitacion::class);
        $request->ejecutar((int) $student, $this->studentDocuments());

        $this->assertSame($before + 1, $notifications->contarNoLeidas($this->adminId));
        $this->assertSame('PENDIENTE', app(ConsultarMisSolicitudesHabilitacion::class)->ejecutar((int) $student)[0]['estado'] ?? null);

        $this->expectException(SolicitudPendienteExistente::class);
        $request->ejecutar((int) $student, $this->studentDocuments());
    }

    public function test_rechazo_notifica_y_permite_volver_a_solicitar(): void
    {
        $student = $this->student();
        $request = app(RegistrarSolicitudHabilitacion::class);
        $id = $request->ejecutar((int) $student, $this->studentDocuments());

        app(RechazarSolicitudHabilitacion::class)->ejecutar($id, (int) $this->adminId, 'Falta el historial académico firmado.');

        $this->assertDatabaseHas('solicitud_habilitacion', ['id_solicitud' => $id, 'estado' => 'rechazada']);
        $this->assertFalse($this->user($student)->tieneRol('TUTOR'));
        $this->assertGreaterThan(0, app(GestionarMisNotificaciones::class)->contarNoLeidas($student));
        $this->assertGreaterThan($id, $request->ejecutar((int) $student, $this->studentDocuments()));
    }

    public function test_aprobacion_otorga_rol_tutor_y_bloquea_nuevas_solicitudes(): void
    {
        $tutor = $this->tutor();

        $this->assertTrue($this->user($tutor)->tieneRol('TUTOR'));
        $this->actingAs($this->user($tutor))->get('/tutor/mis-tutorias')->assertOk();

        $this->expectException(SolicitudYaAprobada::class);
        app(RegistrarSolicitudHabilitacion::class)->ejecutar((int) $tutor, ['curriculum' => 'cv.pdf']);
    }

    public function test_solo_se_aprueba_una_solicitud_pendiente(): void
    {
        $student = $this->student();
        $id = app(RegistrarSolicitudHabilitacion::class)->ejecutar((int) $student, $this->studentDocuments());
        app(AprobarSolicitudHabilitacion::class)->ejecutar($id, (int) $this->adminId);

        $this->expectException(\DomainException::class);
        app(AprobarSolicitudHabilitacion::class)->ejecutar($id, (int) $this->adminId);
    }

    public function test_admin_puede_retirar_el_rol_de_tutor(): void
    {
        $tutor = $this->tutor();

        app(ManageUsers::class)->demoteTutor($tutor, 'Incumplimiento de horarios');

        $this->assertFalse($this->user($tutor)->fresh()->tieneRol('TUTOR'));
        $this->assertDatabaseHas('tutor', ['id_tutor' => (int) $tutor, 'habilitado' => false]);
        $this->actingAs($this->user($tutor)->fresh())->get('/tutor/mis-tutorias')->assertForbidden();
    }

    /** @return array<string, string> */
    private function studentDocuments(): array
    {
        return [
            'carta_solicitud' => 'solicitudes/carta.pdf',
            'curriculum' => 'solicitudes/cv.pdf',
            'registro_academico' => 'solicitudes/registro.pdf',
            'historial_academico' => 'solicitudes/historial.pdf',
        ];
    }
}
