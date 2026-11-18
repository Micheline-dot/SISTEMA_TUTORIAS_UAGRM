<?php

namespace Tests\Integration\Flows;

use App\Business\Cursos\DTO\CourseDraftData;
use App\Business\Cursos\DTO\CreateCourseDraftInput;
use App\Business\Cursos\DTO\PublicCourseDetail;
use App\Business\Cursos\UseCases\CourseNotOwned;
use App\Business\Cursos\UseCases\CourseRuleViolation;
use App\Business\Cursos\UseCases\CreateCourseDraft;
use App\Business\Cursos\UseCases\GetPublicCourseDetail;
use App\Business\Cursos\UseCases\GetTutorPublicProfile;
use App\Business\Cursos\UseCases\ManageCourseCover;
use App\Business\Cursos\UseCases\ManageTutorCourse;
use App\Business\Inscripciones\UseCases\CancelEnrollment;
use App\Business\Inscripciones\UseCases\EnrollStudent;
use App\Business\Inscripciones\UseCases\ListStudentEnrollments;
use App\Business\Usuarios\UseCases\UpdatePersonalData;
use App\Presentation\Livewire\Cursos\GestionarTutoria;
use App\Presentation\Livewire\Cursos\MisTutorias;
use App\Presentation\Livewire\Inscripciones\MisInscripciones;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/** (+) Portada de la tutoría, perfil público del tutor y contacto tras la inscripción. */
final class PortadaPerfilContactoFlowTest extends FlowTestCase
{
    public function test_tutor_sube_cambia_y_quita_la_portada(): void
    {
        $tutor = $this->tutor();
        $course = $this->publishedCourse($tutor);
        $covers = app(ManageCourseCover::class);

        $covers->update($tutor, $course, $this->pngPath('a.png'), 'png');
        $first = DB::table('curso')->where('id_curso', (int) $course)->value('portada');
        Storage::disk('local')->assertExists($first);

        $covers->update($tutor, $course, $this->pngPath('b.jpg'), 'jpg');
        $second = DB::table('curso')->where('id_curso', (int) $course)->value('portada');
        Storage::disk('local')->assertMissing($first);   // la anterior se borra
        Storage::disk('local')->assertExists($second);

        $covers->remove($tutor, $course);
        $this->assertNull(DB::table('curso')->where('id_curso', (int) $course)->value('portada'));
        Storage::disk('local')->assertMissing($second);
    }

    public function test_reglas_de_la_portada(): void
    {
        $tutor = $this->tutor();
        $course = $this->publishedCourse($tutor);
        $covers = app(ManageCourseCover::class);
        $image = $this->pngPath('a.png');

        try {
            $covers->update($tutor, $course, $image, 'gif');
            $this->fail('Aceptó una extensión no permitida.');
        } catch (CourseRuleViolation) {
        }

        try {
            $covers->update($this->tutor(), $course, $image, 'png');
            $this->fail('Otro tutor cambió la portada.');
        } catch (CourseNotOwned) {
        }

        app(ManageTutorCourse::class)->cancel($tutor, $course, 'Motivo de prueba');
        $this->expectException(CourseRuleViolation::class);
        $covers->update($tutor, $course, $image, 'png');
    }

    public function test_portada_visible_segun_estado(): void
    {
        $tutor = $this->tutor();
        $published = $this->publishedCourse($tutor);
        $draft = app(CreateCourseDraft::class)->execute(new CreateCourseDraftInput($tutor, new CourseDraftData('Borrador')))->id;
        $covers = app(ManageCourseCover::class);
        foreach ([$published, $draft] as $id) {
            $covers->update($tutor, $id, $this->pngPath('a.png'), 'png');
        }
        $student = $this->user($this->student());

        $this->actingAs($student)->get("/tutorias/{$published}/portada")->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->actingAs($student)->get("/tutorias/{$draft}/portada")->assertNotFound();
        $this->actingAs($this->user($tutor))->get("/tutorias/{$draft}/portada")->assertOk();
        $this->get("/tutorias/{$published}/portada")->assertOk(); // sesión del tutor
        auth()->logout();
        $this->get("/tutorias/{$published}/portada")->assertRedirect(route('login'));
    }

    public function test_crear_tutoria_con_portada_desde_la_pantalla(): void
    {
        $tutor = $this->tutor();

        Livewire::actingAs($this->user($tutor))
            ->test(MisTutorias::class)
            ->call('nuevaTutoria')
            ->set('titulo', 'Geometría Analítica')
            ->set('categoria', $this->categoryId())
            ->set('portada', $this->png('portada.jpg'))
            ->call('crear')
            ->assertHasNoErrors();

        $path = DB::table('curso')->where('titulo', 'Geometría Analítica')->value('portada');
        $this->assertNotNull($path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_pantalla_gestionar_rechaza_archivo_que_no_es_imagen(): void
    {
        $tutor = $this->tutor();
        $course = $this->publishedCourse($tutor);

        Livewire::actingAs($this->user($tutor))
            ->test(GestionarTutoria::class, ['courseId' => $course])
            ->set('nuevaPortada', UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf'))
            ->call('guardarPortada')
            ->assertHasErrors(['nuevaPortada']);

        Livewire::actingAs($this->user($tutor))
            ->test(GestionarTutoria::class, ['courseId' => $course])
            ->set('nuevaPortada', $this->png('ok.webp'))
            ->call('guardarPortada')
            ->assertHasNoErrors();
        $this->assertNotNull(DB::table('curso')->where('id_curso', (int) $course)->value('portada'));
    }

    public function test_telefono_del_tutor_solo_para_inscritos(): void
    {
        $tutor = $this->tutor();
        app(UpdatePersonalData::class)->execute($tutor, 'Tutor', 'Contacto', '71234567');
        $course = $this->publishedCourse($tutor, 'Contabilidad I');
        $student = $this->student();
        $detail = app(GetPublicCourseDetail::class);

        $this->assertNull($detail->execute($student, $course)->tutorPhone);
        $this->actingAs($this->user($student))->get("/estudiante/tutorias/{$course}")->assertDontSee('71234567');

        app(EnrollStudent::class)->execute($student, $course);
        $d = $detail->execute($student, $course);
        $this->assertSame('71234567', $d->tutorPhone);
        $this->assertSame('https://wa.me/59171234567', $d->tutorWhatsappUrl());
        $this->actingAs($this->user($student))->get("/estudiante/tutorias/{$course}")
            ->assertSee('71234567')->assertSee('Escribir por WhatsApp');
        Livewire::actingAs($this->user($student))->test(MisInscripciones::class)->assertSee('Contactar tutor');

        app(CancelEnrollment::class)->execute($student, $course, 'Ya no puedo asistir');
        $this->assertNull($detail->execute($student, $course)->tutorPhone);
        $history = app(ListStudentEnrollments::class)->execute($student);
        $this->assertNull($history[0]['curso']['tutor']['telefono']);
    }

    /** PNG real de 2x1 px (no requiere la extensión GD). */
    private function png(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAABCAIAAAB7QOjdAAAAEElEQVR4nGNgYGD4z8DAAAAEBAEAlW1fsgAAAABJRU5ErkJggg=='));
    }

    /** @var list<UploadedFile> mantiene vivos los archivos temporales durante la prueba */
    private array $files = [];

    private function pngPath(string $name): string
    {
        return ($this->files[] = $this->png($name))->getRealPath();
    }

    public function test_enlace_whatsapp(): void
    {
        $this->assertSame('https://wa.me/59170000001', PublicCourseDetail::whatsappUrl('7000-0001'));
        $this->assertSame('https://wa.me/59170000001', PublicCourseDetail::whatsappUrl('+591 70000001'));
        $this->assertNull(PublicCourseDetail::whatsappUrl(null));
        $this->assertNull(PublicCourseDetail::whatsappUrl('123'));
    }

    public function test_perfil_publico_del_tutor(): void
    {
        $tutor = $this->tutor('Villagómez');
        app(UpdatePersonalData::class)->execute($tutor, 'Micheline', 'Villagómez', '79998888');
        $this->publishedCourse($tutor, 'Cálculo I');
        $this->publishedCourse($tutor, 'Cálculo II');
        $this->publishedCourse($this->tutor(), 'Otra tutoría');

        $p = app(GetTutorPublicProfile::class)->execute($tutor);
        $this->assertSame('Micheline Villagómez', $p['nombre']);
        $titles = array_map(fn ($c) => $c->title, $p['tutorias']);
        sort($titles);
        $this->assertSame(['Cálculo I', 'Cálculo II'], $titles);
        $this->assertSame(['Matemáticas'], $p['conocimientos']);

        $this->actingAs($this->user($this->student()))->get("/estudiante/tutores/{$tutor}")
            ->assertOk()
            ->assertSee('Micheline Villagómez')
            ->assertSee('Cálculo II')
            ->assertDontSee('Otra tutoría')
            ->assertDontSee('79998888'); // el perfil público no expone el teléfono
    }

    public function test_perfil_de_quien_no_es_tutor_no_existe(): void
    {
        $student = $this->student();
        $viewer = $this->user($this->student());

        $this->actingAs($viewer)->get("/estudiante/tutores/{$student}")->assertNotFound();
        $this->actingAs($viewer)->get('/estudiante/tutores/999999')->assertNotFound();
    }

    public function test_catalogo_enlaza_al_perfil_del_tutor_y_muestra_portada(): void
    {
        $tutor = $this->tutor();
        $course = $this->publishedCourse($tutor, 'Física Moderna');
        app(ManageCourseCover::class)->update($tutor, $course, $this->pngPath('a.png'), 'png');

        $this->actingAs($this->user($this->student()))->get('/estudiante/buscar-tutorias')
            ->assertOk()
            ->assertSee(route('estudiante.tutor', ['tutorId' => $tutor]), false)
            ->assertSee(route('tutoria.portada', ['courseId' => $course]), false);
    }
}
