<?php

namespace Tests\Integration\Flows;

use App\Business\Calificaciones\Exceptions\RatingRuleViolation;
use App\Business\Calificaciones\UseCases\GetTutorReviews;
use App\Business\Calificaciones\UseCases\RateTutor;
use App\Business\Inscripciones\UseCases\EnrollStudent;
use App\Business\Inscripciones\UseCases\VerifyAttendance;
use App\Presentation\Livewire\Calificaciones\CalificarTutoria;
use Livewire\Livewire;

/** CU16 Calificar tutor · CU17 Encuesta · CU18 Reseñas del tutor. */
final class CalificacionesFlowTest extends FlowTestCase
{
    private string $tutor;

    private string $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tutor = $this->tutor();
        $this->course = $this->publishedCourse($this->tutor, 'Bases de Datos');
    }

    public function test_solo_califica_con_participacion_confirmada(): void
    {
        $student = $this->student();
        app(EnrollStudent::class)->execute($student, $this->course);
        app(VerifyAttendance::class)->execute($this->tutor, $this->course, $student);

        $this->expectException(RatingRuleViolation::class);
        app(RateTutor::class)->rate($student, $this->course, 5, 'Excelente explicación');
    }

    public function test_califica_una_vez_y_responde_la_encuesta(): void
    {
        $student = $this->enrolledAndConfirmed();
        $rate = app(RateTutor::class);

        $rate->rate($student, $this->course, 4, 'Muy buena tutoría, clara');
        $rate->answerSurvey($student, $this->course, ['claridad' => 5, 'puntualidad' => 4, 'material' => 4, 'recomienda' => 'SI', 'mejoras' => 'Más ejemplos']);

        $status = $rate->status($student, $this->course);
        $this->assertSame(4, $status['rating']['score']);
        $this->assertTrue($status['rating']['hasForm']);

        $this->expectException(RatingRuleViolation::class);
        $rate->rate($student, $this->course, 5, 'Intento calificar de nuevo');
    }

    public function test_valida_puntuacion_y_resena(): void
    {
        $student = $this->enrolledAndConfirmed();

        foreach ([[0, 'Reseña suficientemente larga'], [6, 'Reseña suficientemente larga'], [4, 'Corta']] as [$score, $comment]) {
            try {
                app(RateTutor::class)->rate($student, $this->course, $score, $comment);
                $this->fail("Aceptó puntuación {$score} / reseña '{$comment}'.");
            } catch (RatingRuleViolation) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_tutor_ve_promedio_distribucion_y_resultados_de_encuesta(): void
    {
        $rate = app(RateTutor::class);
        foreach ([[5, 'SI'], [3, 'NO']] as [$score, $recommend]) {
            $student = $this->enrolledAndConfirmed();
            $rate->rate($student, $this->course, $score, 'Comentario de prueba '.$score);
            $rate->answerSurvey($student, $this->course, ['claridad' => $score, 'puntualidad' => $score, 'material' => $score, 'recomienda' => $recommend]);
        }

        $report = app(GetTutorReviews::class)->execute($this->tutor);

        $this->assertSame(2, $report['count']);
        $this->assertSame(4.0, $report['average']);
        $this->assertSame([5 => 1, 4 => 0, 3 => 1, 2 => 0, 1 => 0], $report['distribution']);
        $this->assertSame(50, $report['survey']['recommend']);
        $this->actingAs($this->user($this->tutor))->get('/tutor/resenas')->assertOk()->assertSee('Comentario de prueba 5');
    }

    public function test_pantalla_calificar(): void
    {
        $student = $this->enrolledAndConfirmed();

        Livewire::actingAs($this->user($student))
            ->test(CalificarTutoria::class, ['courseId' => $this->course])
            ->call('elegir', 5)
            ->set('comentario', 'Tutoría excelente y práctica')
            ->call('enviarCalificacion')
            ->assertHasNoErrors()
            ->assertSee('Enviar encuesta');

        $this->assertDatabaseHas('calificacion_resena', ['id_curso' => (int) $this->course, 'puntuacion' => 5]);
    }

    private function enrolledAndConfirmed(): string
    {
        $student = $this->student();
        app(EnrollStudent::class)->execute($student, $this->course);
        $this->participate($this->tutor, $student, $this->course);

        return $student;
    }
}
