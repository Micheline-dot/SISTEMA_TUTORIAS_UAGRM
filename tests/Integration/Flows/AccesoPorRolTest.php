<?php

namespace Tests\Integration\Flows;

use App\Business\Usuarios\UseCases\ManageUsers;

/** Control de acceso por rol en todas las rutas de la aplicación. */
final class AccesoPorRolTest extends FlowTestCase
{
    private const COMUNES = ['/inicio', '/mi-perfil', '/notificaciones'];

    private const ESTUDIANTE = ['/estudiante/buscar-tutorias', '/estudiante/mis-inscripciones', '/solicitar-habilitacion'];

    private const TUTOR = ['/tutor/mis-tutorias', '/tutor/perfil-academico', '/tutor/resenas', '/tutor/reportes'];

    private const ADMIN = ['/administracion/solicitudes-tutor', '/administracion/tutorias', '/administracion/usuarios', '/administracion/categorias', '/administracion/reportes'];

    public function test_invitado_es_enviado_al_login(): void
    {
        foreach ([...self::COMUNES, ...self::ESTUDIANTE, ...self::TUTOR, ...self::ADMIN] as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
        $this->get('/login')->assertOk();
        $this->get('/registro')->assertOk();
    }

    public function test_estudiante(): void
    {
        $this->assertAccess($this->student(), [...self::COMUNES, ...self::ESTUDIANTE], [...self::TUTOR, ...self::ADMIN]);
    }

    public function test_tutor_conserva_el_area_de_estudiante(): void
    {
        $this->assertAccess($this->tutor(), [...self::COMUNES, '/estudiante/buscar-tutorias', ...self::TUTOR], self::ADMIN);
    }

    public function test_administrador(): void
    {
        $this->assertAccess($this->adminId, [...self::COMUNES, ...self::ADMIN], [...self::ESTUDIANTE, ...self::TUTOR]);
    }

    public function test_usuario_desactivado_pierde_el_acceso(): void
    {
        $student = $this->student();
        app(ManageUsers::class)->setActive($this->adminId, $student, false);

        $this->actingAs($this->user($student))->get('/estudiante/mis-inscripciones')->assertRedirect(route('login'));
    }

    public function test_menu_muestra_solo_las_secciones_del_rol(): void
    {
        $this->actingAs($this->user($this->student()))->get('/inicio')
            ->assertSee('Área de estudiante')->assertDontSee('Área de tutor')->assertDontSee('Administración');
        $this->actingAs($this->user($this->adminId))->get('/inicio')
            ->assertSee('Administración')->assertDontSee('Área de estudiante');
    }

    /**
     * @param  list<string>  $allowed
     * @param  list<string>  $denied
     */
    private function assertAccess(string $userId, array $allowed, array $denied): void
    {
        $user = $this->user($userId);
        foreach ($allowed as $uri) {
            $this->actingAs($user)->get($uri)->assertOk();
        }
        foreach ($denied as $uri) {
            $this->actingAs($user)->get($uri)->assertForbidden();
        }
    }
}
