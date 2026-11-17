<?php

namespace App\Presentation\Livewire\Reportes;

use App\Business\Reportes\DTO\ReportPeriod;
use App\Business\Reportes\Exceptions\InvalidReportPeriod;
use App\Business\Reportes\UseCases\GetAdminReport;
use App\Presentation\Http\CsvResponse;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** CU22 Generar reportes (administrador). */
final class ReportesAdmin extends Component
{
    #[Url(except: '')]
    public string $desde = '';

    #[Url(except: '')]
    public string $hasta = '';

    protected GetAdminReport $reporte;

    public function boot(GetAdminReport $reporte): void
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

        $t = $r['totales'];
        $rows = [
            ['Reporte general - Sistema de Tutorías UAGRM'],
            ['Periodo', $this->etiquetaPeriodo($r['periodo'])],
            [],
            ['Indicador', 'Valor'],
            ['Usuarios activos', $t['usuarios_activos']],
            ['Estudiantes', $t['estudiantes']],
            ['Tutores habilitados', $t['tutores']],
            ['Solicitudes de tutor pendientes', $t['solicitudes_pendientes']],
            ['Tutorías publicadas', $t['cursos']['PUBLICADO']],
            ['Tutorías concluidas', $t['cursos']['FINALIZADO']],
            ['Tutorías canceladas', $t['cursos']['CANCELADO']],
            ['Borradores', $t['cursos']['BORRADOR']],
            ['Inscripciones', $t['inscripciones']],
            ['Inscripciones vigentes o completadas', $t['inscripciones_efectivas']],
            ['Participaciones confirmadas', $t['confirmadas']],
            ['Cancelaciones', $t['cancelaciones']],
            ['Calificaciones', $t['calificaciones']],
            ['Calificación promedio', $t['promedio_calificacion']],
            [],
            ['Mejores tutores'],
            ['Tutor', 'Promedio', 'Calificaciones', 'Tutorías', 'Inscritos'],
        ];
        foreach ($r['mejores_tutores'] as $x) {
            $rows[] = [$x['nombre'], $x['promedio'], $x['calificaciones'], $x['tutorias'], $x['inscritos']];
        }
        $rows[] = [];
        $rows[] = ['Demanda por categoría'];
        $rows[] = ['Categoría', 'Inscripciones', 'Tutorías'];
        foreach ($r['categorias'] as $x) {
            $rows[] = [$x['nombre'], $x['inscripciones'], $x['tutorias']];
        }
        $rows[] = [];
        $rows[] = ['Demanda por facultad'];
        $rows[] = ['Facultad', 'Inscripciones', 'Estudiantes'];
        foreach ($r['facultades'] as $x) {
            $rows[] = [$x['nombre'], $x['inscripciones'], $x['estudiantes']];
        }

        return CsvResponse::make('reporte-general-'.now()->format('Y-m-d').'.csv', $rows);
    }

    /** @return array<string, mixed>|null */
    private function generar(): ?array
    {
        $this->resetErrorBag('periodo');

        try {
            return $this->reporte->execute(ReportPeriod::fromInput($this->desde, $this->hasta));
        } catch (InvalidReportPeriod $e) {
            $this->addError('periodo', $e->getMessage());

            return null;
        }
    }

    private function etiquetaPeriodo(ReportPeriod $p): string
    {
        return $p->isAll() ? 'Todo el historial' : ($p->from ?? 'inicio').' a '.($p->to ?? 'hoy');
    }

    public function render(): View
    {
        $r = $this->generar() ?? $this->reporte->execute();

        return view('livewire.reportes.reportes-admin', [
            'r' => $r,
            'periodo' => $this->etiquetaPeriodo($r['periodo']),
        ])->layout('layouts.admin')->title('Reportes');
    }
}
