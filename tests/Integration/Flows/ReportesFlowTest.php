<?php

namespace Tests\Integration\Flows;

use App\Business\Calificaciones\UseCases\RateTutor;
use App\Business\Cursos\UseCases\ManageTutorCourse;
use App\Business\Inscripciones\UseCases\CancelEnrollment;
use App\Business\Inscripciones\UseCases\EnrollStudent;
use App\Business\Reportes\DTO\ReportPeriod;
use App\Business\Reportes\Exceptions\InvalidReportPeriod;
use App\Business\Reportes\UseCases\GetAdminReport;
use App\Business\Reportes\UseCases\GetTutorReport;
use App\Presentation\Livewire\Reportes\ReportesAdmin;
use Livewire\Livewire;

/** CU22 Reportes del administrador · CU23 Reportes del tutor. */
final class ReportesFlowTest extends FlowTestCase
{
    private string $bestTutor;

    private string $otherTutor;

    private string $courseA;

    protected function setUp(): void
    {
        parent::setUp();

        // Tutor 1: A (3 inscritos de 4, calificaciones 5 y 4) y B (1 inscrito, 1 cancelación, calificación 3)
        // Tutor 2: Z (2 inscritos, calificaciones 5 y 5) → 6 inscripciones efectivas en total
        $this->otherTutor = $this->tutor('Uno');
        $this->bestTutor = $this->tutor('Dos');
        $this->courseA = $this->publishedCourse($this->otherTutor, 'Curso A', 4, 0, 0);
        $courseB = $this->publishedCourse($this->otherTutor, 'Curso B', 10, 1);
        $courseZ = $this->publishedCourse($this->bestTutor, 'Curso Z', 5, 0);

        $s = [$this->student(1), $this->student(1), $this->student(1), $this->student(2)];
        foreach ([0, 1, 2] as $i) {
            app(EnrollStudent::class)->execute($s[$i], $this->courseA);
        }
        app(EnrollStudent::class)->execute($s[0], $courseB);
        app(EnrollStudent::class)->execute($s[3], $courseB);
        app(CancelEnrollment::class)->execute($s[3], $courseB, 'Cruce de horario');
        app(EnrollStudent::class)->execute($s[1], $courseZ);
        app(EnrollStudent::class)->execute($s[3], $courseZ);

        $rate = app(RateTutor::class);
        foreach ([[$this->otherTutor, $s[0], $this->courseA, 5], [$this->otherTutor, $s[1], $this->courseA, 4], [$this->otherTutor, $s[0], $courseB, 3], [$this->bestTutor, $s[1], $courseZ, 5], [$this->bestTutor, $s[3], $courseZ, 5]] as [$t, $st, $c, $score]) {
            $this->participate($t, $st, $c);
            $rate->rate($st, $c, $score, 'Reseña de prueba número '.$score);
        }
        app(ManageTutorCourse::class)->finalize($this->otherTutor, $this->courseA);
    }

    public function test_reporte_del_tutor(): void
    {
        $r = app(GetTutorReport::class)->execute($this->otherTutor);

        $this->assertSame(1, $r['resumen']['publicadas']);
        $this->assertSame(1, $r['resumen']['concluidas']);
        $this->assertSame(4, $r['resumen']['total_inscritos']);
        $this->assertSame(2.0, $r['resumen']['promedio_inscritos']);
        $this->assertSame(4.0, $r['resumen']['promedio_calificacion']);
        $this->assertSame($this->courseA, $r['mejor_tutoria']['tutoria']['id']);
        $this->assertSame(75, $r['tutorias'][array_search($this->courseA, array_column($r['tutorias'], 'id'))]['ocupacion']);
    }

    public function test_reporte_del_administrador(): void
    {
        $r = app(GetAdminReport::class)->execute();

        $this->assertSame(6, $r['totales']['inscripciones_efectivas']);
        $this->assertSame(1, $r['totales']['cancelaciones']);
        $this->assertSame(5, $r['totales']['calificaciones']);
        $this->assertSame($this->bestTutor, $r['mejor_tutor']['id']);
        $this->assertSame(5.0, $r['mejor_tutor']['promedio']);
        $this->assertSame(5, $r['categoria_mas_solicitada']['inscripciones']);
        $this->assertSame($this->faculty(1), $r['facultad_mas_solicitada']['nombre']);
    }

    public function test_periodo_filtra_y_se_valida(): void
    {
        $future = ReportPeriod::fromInput($this->date(1), $this->date(10));
        $r = app(GetAdminReport::class)->execute($future);

        $this->assertSame(0, $r['totales']['inscripciones']);
        $this->assertNull($r['mejor_tutor']);

        $this->expectException(InvalidReportPeriod::class);
        ReportPeriod::fromInput($this->date(10), $this->date(1));
    }

    public function test_pantallas_y_exportacion(): void
    {
        $this->actingAs($this->user($this->adminId))->get('/administracion/reportes')->assertOk()->assertSee('Tutor Dos');
        $this->actingAs($this->user($this->otherTutor))->get('/tutor/reportes')->assertOk()->assertSee('Curso A')->assertDontSee('Curso Z');
        $this->actingAs($this->user($this->otherTutor))->get('/administracion/reportes')->assertForbidden();

        Livewire::actingAs($this->user($this->adminId))
            ->test(ReportesAdmin::class)
            ->call('exportarCsv')
            ->assertFileDownloaded('reporte-general-'.date('Y-m-d').'.csv');
    }
}
