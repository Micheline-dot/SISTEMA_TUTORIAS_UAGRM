<?php

namespace Tests\Integration\Flows;

use App\Business\Configuracion\Exceptions\InvalidLogo;
use App\Business\Configuracion\UseCases\ManageSystemLogo;
use App\Presentation\Livewire\Administracion\ConfigurarLogo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/** (+) Logo institucional configurable por el administrador. */
final class LogoSistemaFlowTest extends FlowTestCase
{
    /** @var list<UploadedFile> */
    private array $files = [];

    public function test_sin_logo_se_muestra_el_nombre_en_texto(): void
    {
        $this->get('/login')->assertOk()->assertSee('TUTORÍAS', false)->assertDontSee('/sistema/logo');
        $this->get('/sistema/logo')->assertNotFound();
    }

    public function test_admin_sube_logo_y_aparece_en_login_y_menu(): void
    {
        app(ManageSystemLogo::class)->update($this->adminId, $this->pngPath(), 'png');

        $this->get('/login')->assertOk()->assertSee('/sistema/logo?v=', false);
        $this->get('/registro')->assertOk()->assertSee('/sistema/logo?v=', false);
        $this->get('/sistema/logo')->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->actingAs($this->user($this->student()))->get('/inicio')->assertOk()->assertSee('/sistema/logo?v=', false);
    }

    public function test_reemplazar_borra_el_anterior_y_quitar_vuelve_al_texto(): void
    {
        $logos = app(ManageSystemLogo::class);
        $logos->update($this->adminId, $this->pngPath(), 'png');
        $first = $logos->currentPath();
        $logos->update($this->adminId, $this->pngPath(), 'webp');
        $second = $logos->currentPath();

        $this->assertNotSame($first, $second);
        Storage::disk('local')->assertMissing($first);
        Storage::disk('local')->assertExists($second);

        $logos->remove($this->adminId);
        $this->assertFalse($logos->hasLogo());
        Storage::disk('local')->assertMissing($second);
        $this->get('/login')->assertDontSee('/sistema/logo');
    }

    public function test_solo_el_admin_y_solo_imagenes_permitidas(): void
    {
        $logos = app(ManageSystemLogo::class);

        try {
            $logos->update($this->adminId, $this->pngPath(), 'svg');
            $this->fail('Aceptó un SVG.');
        } catch (InvalidLogo) {
        }

        $this->expectException(InvalidLogo::class);
        $logos->update($this->tutor(), $this->pngPath(), 'png');
    }

    public function test_pantalla_del_administrador(): void
    {
        $admin = $this->user($this->adminId);

        $this->actingAs($admin)->get('/administracion/logo')->assertOk()->assertSee('Logo del sistema');
        $this->actingAs($this->user($this->student()))->get('/administracion/logo')->assertForbidden();

        Livewire::actingAs($admin)->test(ConfigurarLogo::class)
            ->set('logo', UploadedFile::fake()->create('logo.pdf', 5, 'application/pdf'))
            ->call('guardar')
            ->assertHasErrors(['logo']);

        Livewire::actingAs($admin)->test(ConfigurarLogo::class)
            ->set('logo', $this->png())
            ->call('guardar')
            ->assertHasNoErrors()
            ->call('quitar');

        $this->assertFalse(app(ManageSystemLogo::class)->hasLogo());
    }

    public function test_acepta_jfif_porque_se_valida_el_contenido_y_no_el_nombre(): void
    {
        $jfif = UploadedFile::fake()->createWithContent('Gemini_Generated_Image.jfif', base64_decode(self::JPEG));
        Livewire::actingAs($this->user($this->adminId))->test(ConfigurarLogo::class)
            ->set('logo', $jfif)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertStringEndsWith('.jpg', app(ManageSystemLogo::class)->currentPath());
        $this->get('/sistema/logo')->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        $tutor = $this->tutor();
        $course = $this->publishedCourse($tutor);
        Livewire::actingAs($this->user($tutor))->test(\App\Presentation\Livewire\Cursos\GestionarTutoria::class, ['courseId' => $course])
            ->set('nuevaPortada', UploadedFile::fake()->createWithContent('portada.jfif', base64_decode(self::JPEG)))
            ->call('guardarPortada')
            ->assertHasNoErrors();
        $this->assertStringEndsWith('.jpg', (string) \DB::table('curso')->where('id_curso', (int) $course)->value('portada'));
    }

    public function test_fondo_del_login_se_sube_se_muestra_y_se_quita(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('/sistema/fondo-login');
        $this->get('/sistema/fondo-login')->assertNotFound();

        $admin = $this->user($this->adminId);
        Livewire::actingAs($admin)->test(ConfigurarLogo::class)
            ->set('fondo', UploadedFile::fake()->createWithContent('campus.jfif', base64_decode(self::JPEG)))
            ->call('guardarFondo')
            ->assertHasNoErrors();

        $fondos = app(\App\Business\Configuracion\UseCases\ManageLoginBackground::class);
        $path = $fondos->currentPath();
        $this->assertStringStartsWith('sistema/fondo_login_', $path);
        $this->assertFalse(app(ManageSystemLogo::class)->hasLogo()); // el logo es independiente

        auth()->logout();
        $this->get('/login')->assertOk()->assertSee('class="con-fondo"', false)->assertSee('/sistema/fondo-login?v=', false);
        $this->get('/registro')->assertOk()->assertSee('/sistema/fondo-login?v=', false);
        $this->get('/sistema/fondo-login')->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        Livewire::actingAs($admin)->test(ConfigurarLogo::class)->call('quitarFondo')->assertHasNoErrors();
        $this->assertFalse($fondos->exists());
        Storage::disk('local')->assertMissing($path);

        $this->expectException(InvalidLogo::class);
        $fondos->update($this->student(), $this->pngPath(), 'png');
    }

    private const JPEG = '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';

    private function png(): UploadedFile
    {
        return $this->files[] = UploadedFile::fake()->createWithContent(
            'logo.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAABCAIAAAB7QOjdAAAAEElEQVR4nGNgYGD4z8DAAAAEBAEAlW1fsgAAAABJRU5ErkJggg=='),
        );
    }

    private function pngPath(): string
    {
        return $this->png()->getRealPath();
    }
}
