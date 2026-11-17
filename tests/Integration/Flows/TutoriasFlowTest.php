<?php

namespace Tests\Integration\Flows;

use App\Business\Administracion\Exceptions\CancellationReasonRequired;
use App\Business\Administracion\UseCases\CancelAdminCourse;
use App\Business\Cursos\DTO\CourseDraftData;
use App\Business\Cursos\DTO\CourseModality;
use App\Business\Cursos\DTO\CreateCourseDraftInput;
use App\Business\Cursos\UseCases\AcademicProfileIncomplete;
use App\Business\Cursos\UseCases\CourseRuleViolation;
use App\Business\Cursos\UseCases\CreateCourseDraft;
use App\Business\Cursos\UseCases\ManageTutorCourse;
use App\Business\Cursos\UseCases\PublishCourseDraft;
use App\Business\Habilitaciones\UseCases\AprobarSolicitudHabilitacion;
use App\Business\Habilitaciones\UseCases\RegistrarSolicitudHabilitacion;
use App\Business\Inscripciones\UseCases\EnrollStudent;
use App\Business\Notificaciones\UseCases\GestionarMisNotificaciones;
use App\Business\Usuarios\DTO\CompleteAcademicDataInput;
use App\Business\Usuarios\UseCases\CompleteAcademicData;
use App\Presentation\Livewire\Cursos\MisTutorias;
use Livewire\Livewire;

/** CU07 Crear tutoría · CU08 Temario · CU09 Publicar · CU10 Gestionar publicada. */
final class TutoriasFlowTest extends FlowTestCase
{
    public function test_crear_borrador_exige_perfil_academico_completo(): void
    {
        $id = $this->register('Tutor');
        app(CompleteAcademicData::class)->execute(new CompleteAcademicDataInput($id, $this->faculty(), 'DOCENTE', null, null, 'DOC-X'));
        $request = app(RegistrarSolicitudHabilitacion::class)->ejecutar((int) $id, ['curriculum' => 'cv.pdf']);
        app(AprobarSolicitudHabilitacion::class)->ejecutar($request, (int) $this->adminId);

        $this->expectException(AcademicProfileIncomplete::class);
        app(CreateCourseDraft::class)->execute(new CreateCourseDraftInput($id, new CourseDraftData('Álgebra')));
    }

    public function test_publicar_valida_requisitos_minimos(): void
    {
        $tutor = $this->tutor();
        $draft = app(CreateCourseDraft::class)->execute(new CreateCourseDraftInput($tutor, new CourseDraftData('Álgebra')));

        try {
            app(PublishCourseDraft::class)->execute($draft->id, $tutor);
            $this->fail('Se publicó un borrador incompleto.');
        } catch (CourseRuleViolation $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        $this->assertDatabaseHas('curso', ['id_curso' => (int) $draft->id, 'estado' => 'BORRADOR']);
    }

    public function test_tutoria_completa_se_publica_y_aparece_en_el_catalogo(): void
    {
        $tutor = $this->tutor();
        $course = $this->publishedCourse($tutor, 'Física General');

        $this->assertDatabaseHas('curso', ['id_curso' => (int) $course, 'estado' => 'PUBLICADO']);
        $this->actingAs($this->user($this->student()))
            ->get('/estudiante/buscar-tutorias?q=Física')
            ->assertOk()
            ->assertSee('Física General');
    }

    public function test_cupos_no_bajan_de_los_inscritos(): void
    {
        $tutor = $this->tutor();
        $course = $this->publishedCourse($tutor, seats: 5);
        app(EnrollStudent::class)->execute($this->student(), $course);
        app(EnrollStudent::class)->execute($this->student(), $course);

        $manage = app(ManageTutorCourse::class);
        $manage->updatePublished($tutor, $course, 3, $this->date(1), $this->date(30));
        $this->assertDatabaseHas('curso', ['id_curso' => (int) $course, 'cupos' => 3]);

        $this->expectException(CourseRuleViolation::class);
        $manage->updatePublished($tutor, $course, 1, $this->date(1), $this->date(30));
    }

    public function test_cancelar_tutoria_notifica_a_los_inscritos(): void
    {
        $tutor = $this->tutor();
        $course = $this->publishedCourse($tutor);
        $student = $this->student();
        app(EnrollStudent::class)->execute($student, $course);
        $before = app(GestionarMisNotificaciones::class)->contarNoLeidas($student);

        app(ManageTutorCourse::class)->cancel($tutor, $course, 'Viaje imprevisto del tutor');

        $this->assertDatabaseHas('curso', ['id_curso' => (int) $course, 'estado' => 'CANCELADO', 'motivo_cancelacion' => 'Viaje imprevisto del tutor']);
        $this->assertDatabaseHas('curso_inscripcion', ['id_curso' => (int) $course, 'estado' => 'cancelada']);
        $this->assertSame($before + 1, app(GestionarMisNotificaciones::class)->contarNoLeidas($student));
    }

    public function test_finalizar_solo_tutorias_iniciadas(): void
    {
        $tutor = $this->tutor();
        $future = $this->publishedCourse($tutor, 'Futura', startInDays: 5);
        $today = $this->publishedCourse($tutor, 'De hoy', startInDays: 0);
        $manage = app(ManageTutorCourse::class);

        $manage->finalize($tutor, $today);
        $this->assertDatabaseHas('curso', ['id_curso' => (int) $today, 'estado' => 'FINALIZADO']);

        $this->expectException(CourseRuleViolation::class);
        $manage->finalize($tutor, $future);
    }

    public function test_otro_tutor_no_gestiona_tutorias_ajenas(): void
    {
        $course = $this->publishedCourse($this->tutor());
        $other = $this->tutor();

        $this->actingAs($this->user($other))->get("/tutor/tutorias/{$course}")->assertNotFound();
    }

    public function test_admin_cancela_con_motivo_obligatorio(): void
    {
        $course = $this->publishedCourse($this->tutor());

        try {
            app(CancelAdminCourse::class)->execute($course, '   ');
            $this->fail('Se canceló sin motivo.');
        } catch (CancellationReasonRequired) {
        }

        app(CancelAdminCourse::class)->execute($course, 'Contenido inapropiado');
        $this->assertDatabaseHas('curso', ['id_curso' => (int) $course, 'estado' => 'CANCELADO']);
    }

    public function test_categorias_no_se_duplican_ni_se_borran_en_uso(): void
    {
        $categories = app(\App\Business\Categorias\UseCases\ManageCategories::class);
        $new = $categories->create('Robótica');
        $categories->rename($new, 'Robótica educativa');
        $this->assertDatabaseHas('categoria', ['id_categoria' => (int) $new, 'nombre' => 'Robótica educativa']);

        try {
            $categories->create('robótica educativa');
            $this->fail('Se creó una categoría duplicada.');
        } catch (\App\Business\Categorias\Exceptions\CategoryRuleViolation) {
        }

        $this->publishedCourse($this->tutor());
        $this->expectException(\App\Business\Categorias\Exceptions\CategoryRuleViolation::class);
        $categories->delete($this->categoryId());
    }

    public function test_notificaciones_se_marcan_como_leidas(): void
    {
        $tutor = $this->tutor();
        $notifications = app(GestionarMisNotificaciones::class);

        $this->assertGreaterThan(0, $notifications->contarNoLeidas($tutor)); // aviso de solicitud aprobada
        $notifications->marcarTodasLeidas($tutor);
        $this->assertSame(0, $notifications->contarNoLeidas($tutor));
        $this->actingAs($this->user($tutor))->get('/notificaciones')->assertOk()->assertSee('aprob');
    }

    public function test_pantalla_mis_tutorias_crea_borrador(): void
    {
        $tutor = $this->tutor();

        Livewire::actingAs($this->user($tutor))
            ->test(MisTutorias::class)
            ->call('nuevaTutoria')
            ->set('titulo', 'Estadística básica')
            ->set('categoria', $this->categoryId())
            ->call('crear')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('curso', ['id_tutor' => (int) $tutor, 'titulo' => 'Estadística básica', 'estado' => 'BORRADOR']);
    }
}
