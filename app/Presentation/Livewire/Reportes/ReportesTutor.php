<?php

namespace App\Presentation\Livewire\Reportes;

use App\Business\Cursos\UseCases\TutorNotEnabled;
use App\Business\Reportes\DTO\ReportPeriod;
use App\Business\Reportes\Exceptions\InvalidReportPeriod;
use App\Business\Reportes\UseCases\GetTutorReport;
use App\Presentation\Http\CsvResponse;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** CU23 Generar reportes (tutor). */
final class ReportesTutor extends Component
{
    public const ESTADOS = [
        'PUBLICADO' => 'Publicada',
        'FINALIZADO' => 'Concluida',
        'CANCELADO' => 'Cancelada',
    ];

    #[Url(except: '')]
    public string $desde = '';

    #[Url(except: '')]
    public string $hasta = '';

    protected GetTutorReport $reporte;

    public function boot(GetTutorReport $reporte): void
    {
        $this->reporte = $reporte;
    }

    public function limpiar(): void
    {
        $this->desde = '';
        $this->hasta = '';
    }

    public function exportarCsv(): ?StreamedResponse
    {
        $r = $this->generar();
        if ($r === null) {
            return null;
        }

        $s = $r['resumen'];
        $rows = [
            ['Reporte de tutorías - '.auth()->user()?->nombres.' '.auth()->user()?->apellidos],
            ['Periodo', $r['periodo']->isAll() ? 'Todo el historial' : ($r['periodo']->from ?? 'inicio').' a '.($r['periodo']->to ?? 'hoy')],
            [],
            ['Indicador', 'Valor'],
            ['Tutorías publicadas', $s['publicadas']],
            ['Tutorías concluidas', $s['concluidas']],
            ['Tutorías canceladas', $s['canceladas']],
            ['Borradores', $s['borradores']],
            ['Total de inscritos', $s['total_inscritos']],
            ['Promedio de inscritos por tutoría', $s['promedio_inscritos']],
            ['Ocupación promedio (%)', $s['ocupacion_promedio']],
            ['Participaciones confirmadas', $s['confirmados']],
            ['Cancelaciones', $s['cancelaciones']],
            ['Calificación promedio', $s['promedio_calificacion']],
            ['Mejor tutoría', $r['mejor_tutoria']['tutoria']['titulo'] ?? '—'],
            [],
            ['Tutoría', 'Estado', 'Categoría', 'Inicio', 'Fin', 'Cupos', 'Inscritos', 'Ocupación (%)', 'Confirmados', 'Cancelaciones', 'Retirados', 'Promedio', 'Calificaciones'],
        ];
        foreach ($r['tutorias'] as $c) {
            $rows[] = [
                $c['titulo'], self::ESTADOS[$c['estado']] ?? $c['estado'], $c['categoria'], $c['fecha_inicio'], $c['fecha_finalizacion'],
                $c['cupos'], $c['inscritos'], $c['ocupacion'], $c['confirmados'], $c['cancelaciones'], $c['retirados'], $c['promedio'], $c['calificaciones'],
            ];
        }

        return CsvResponse::make('reporte-tutorias-'.now()->format('Y-m-d').'.csv', $rows);
    }

    /** @return array<string, mixed>|null */
    private function generar(): ?array
    {
        $this->resetErrorBag('periodo');

        try {
            return $this->reporte->execute((string) auth()->id(), ReportPeriod::fromInput($this->desde, $this->hasta));
        } catch (InvalidReportPeriod $e) {
            $this->addError('periodo', $e->getMessage());

            return null;
        } catch (TutorNotEnabled) {
            abort(403);
        }
    }

    public function render(): View
    {
        $r = $this->generar();
        if ($r === null) {
            try {
                $r = $this->reporte->execute((string) auth()->id());
            } catch (TutorNotEnabled) {
                abort(403);
            }
        }

        return view('livewire.reportes.reportes-tutor', [
            'r' => $r,
            'estados' => self::ESTADOS,
        ])->layout('layouts.dashboard')->title('Mis reportes');
    }
}
