<?php

namespace Tests\Integration\Flows;

use App\Business\Cursos\DTO\CourseCatalogFilters;
use App\Business\Cursos\UseCases\GetPublicCourseDetail;
use App\Business\Cursos\UseCases\SearchPublishedCourses;
use App\Business\Inscripciones\Exceptions\AlreadyEnrolled;
use App\Business\Inscripciones\Exceptions\CannotEnrollOwnCourse;
use App\Business\Inscripciones\Exceptions\CourseFull;
use App\Business\Inscripciones\Exceptions\EnrollmentRuleViolation;
use App\Business\Inscripciones\UseCases\CancelEnrollment;
use App\Business\Inscripciones\UseCases\EnrollStudent;
use App\Business\Inscripciones\UseCases\RemoveEnrolledStudent;
use App\Business\Notificaciones\UseCases\GestionarMisNotificaciones;
use App\Presentation\Livewire\Inscripciones\MisInscripciones;
use Livewire\Livewire;

/** CU11 Buscar · CU12 Filtrar · CU13 Detalle · CU14 Inscripción · CU15 Retirar estudiante. */
final class InscripcionesFlowTest extends FlowTestCase
{
    public function test_busqueda_y_filtro_por_categoria(): void
    {
        $tutor = $this->tutor('Rojas');
        $this->publishedCourse($tutor, 'Cálculo Integral', category: 0);
        $this->publishedCourse($tutor, 'Programación Web', category: 1);

        $search = app(SearchPublishedCourses::class);
        $byText = $search->execute(new CourseCatalogFilters(search: 'cálculo'));
        $byTutor = $search->execute(new CourseCatalogFilters(search: 'rojas'));
        $byCategory = $search->execute(new CourseCatalogFilters(categoryId: $this->categoryId(1)));

        $this->assertSame(['Cálculo Integral'], array_map(fn ($c) => $c->title, $byText));
        $this->assertCount(2, $byTutor);
        $this->assertSame(['Programación Web'], array_map(fn ($c) => $c->title, $byCategory));
    }

    public function test_inscripcion_y_reglas_de_cupo(): void
    {
        $tutor = $this->tutor();
        $course = $this->publishedCourse($tutor, seats: 1);
        $enroll = app(EnrollStudent::class);
        $first = $this->student();
        $enroll->execute($first, $course);

        $detail = app(GetPublicCourseDetail::class)->execute($first, $course);
        $this->assertTrue($detail->viewerIsEnrolled);
        $this->assertSame(0, $detail->availableSeats());

        try {
            $enroll->execute($first, $course);
            $this->fail('Se inscribió dos veces.');
        } catch (AlreadyEnrolled) {
        }

        try {
            $enroll->execute($tutor, $course);
            $this->fail('El tutor se inscribió en su propia tutoría.');
        } catch (CannotEnrollOwnCourse) {
        }

        $this->expectException(CourseFull::class);
        $enroll->execute($this->student(), $course);
    }

    public function test_cancelar_inscripcion_libera_cupo_y_avisa_al_tutor(): void
    {
        $tutor = $this->tutor();
        $course = $this->publishedCourse($tutor, seats: 1);
        $student = $this->student();
        app(EnrollStudent::class)->execute($student, $course);
        $before = app(GestionarMisNotificaciones::class)->contarNoLeidas($tutor);

        try {
            app(CancelEnrollment::class)->execute($student, $course, 'no');
            $this->fail('Se canceló sin un motivo válido.');
        } catch (EnrollmentRuleViolation) {
        }

        app(CancelEnrollment::class)->execute($student, $course, 'Cruce con mis clases');

        $this->assertSame($before + 1, app(GestionarMisNotificaciones::class)->contarNoLeidas($tutor));
        $this->assertDatabaseHas('inscripcion', ['id_usuario' => (int) $student, 'motivo_cancelacion' => 'Cruce con mis clases']);
        app(EnrollStudent::class)->execute($this->student(), $course); // el cupo quedó libre
    }

    public function test_asistencia_y_confirmacion_de_participacion(): void
    {
        $tutor = $this->tutor();
        $course = $this->publishedCourse($tutor);
        $student = $this->student();
        app(EnrollStudent::class)->execute($student, $course);

        $this->participate($tutor, $student, $course);

        $this->assertDatabaseHas('curso_inscripcion', ['id_curso' => (int) $course, 'estado' => 'confirmada']);
    }

    public function test_estudiante_retirado_no_puede_volver_a_inscribirse(): void
    {
        $tutor = $this->tutor();
        $course = $this->publishedCourse($tutor);
        $student = $this->student();
        app(EnrollStudent::class)->execute($student, $course);

        app(RemoveEnrolledStudent::class)->execute($tutor, $course, $student, 'Comportamiento inadecuado');

        $this->assertDatabaseHas('curso_inscripcion', ['id_curso' => (int) $course, 'estado' => 'eliminada']);
        $this->assertGreaterThan(0, app(GestionarMisNotificaciones::class)->contarNoLeidas($student));
        $this->expectException(EnrollmentRuleViolation::class);
        app(EnrollStudent::class)->execute($student, $course);
    }

    public function test_pantallas_detalle_y_mis_inscripciones(): void
    {
        $course = $this->publishedCourse($this->tutor(), 'Química Orgánica');
        $student = $this->user($this->student());

        $this->actingAs($student)->get("/estudiante/tutorias/{$course}")->assertOk()->assertSee('Química Orgánica');
        app(EnrollStudent::class)->execute((string) $student->getKey(), $course);

        Livewire::actingAs($student)
            ->test(MisInscripciones::class)
            ->assertSee('Química Orgánica')
            ->call('abrirCancelar', $course)
            ->set('motivo', 'Ya no tengo tiempo')
            ->call('cancelarInscripcion')
            ->assertHasNoErrors()
            ->set('vista', 'historial')
            ->assertSee('Ya no tengo tiempo');
    }
}
