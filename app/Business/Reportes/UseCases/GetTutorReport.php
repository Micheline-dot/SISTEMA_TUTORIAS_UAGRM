<?php

namespace App\Business\Reportes\UseCases;

use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Cursos\UseCases\TutorNotEnabled;
use App\Business\Reportes\Contracts\ReportRepository;
use App\Business\Reportes\DTO\ReportPeriod;

/**
 * CU23 Generar reportes (tutor): mejor tutoría, promedio de inscritos,
 * tutorías publicadas/concluidas y estadísticas por tutoría.
 */
final class GetTutorReport
{
    public function __construct(
        private readonly ReportRepository $reports,
        private readonly TutorEligibility $tutors,
    ) {
    }

    /**
     * @return array{
     *   periodo: ReportPeriod,
     *   resumen: array{publicadas: int, concluidas: int, canceladas: int, borradores: int,
     *     total_inscritos: int, promedio_inscritos: ?float, ocupacion_promedio: ?int,
     *     confirmados: int, cancelaciones: int, calificaciones: int, promedio_calificacion: ?float},
     *   mejor_tutoria: ?array{tutoria: array<string, mixed>, criterio: string},
     *   tutorias: list<array<string, mixed>>
     * }
     */
    public function execute(string $tutorId, ?ReportPeriod $period = null): array
    {
        if (! $this->tutors->isEnabledTutor($tutorId)) {
            throw new TutorNotEnabled('Only enabled tutors can see their reports.');
        }

        $period ??= ReportPeriod::all();
        $tutorias = array_map(function (array $c): array {
            $c['ocupacion'] = $c['cupos'] !== null && $c['cupos'] > 0
                ? (int) round(min(100, $c['inscritos'] * 100 / $c['cupos']))
                : null;

            return $c;
        }, $this->reports->tutorCourseStats($tutorId, $period));

        $porEstado = fn (string $estado) => count(array_filter($tutorias, fn ($c) => $c['estado'] === $estado));

        // El promedio de inscritos considera tutorías que llegaron a dictarse o están vigentes.
        $dictadas = array_values(array_filter($tutorias, fn ($c) => in_array($c['estado'], ['PUBLICADO', 'FINALIZADO'], true)));
        $ocupaciones = array_values(array_filter(array_column($dictadas, 'ocupacion'), 'is_int'));

        $calificaciones = array_sum(array_column($tutorias, 'calificaciones'));
        $sumaPuntos = array_sum(array_map(fn ($c) => ($c['promedio'] ?? 0) * $c['calificaciones'], $tutorias));

        return [
            'periodo' => $period,
            'resumen' => [
                'publicadas' => $porEstado('PUBLICADO'),
                'concluidas' => $porEstado('FINALIZADO'),
                'canceladas' => $porEstado('CANCELADO'),
                'borradores' => $this->reports->tutorDraftCount($tutorId),
                'total_inscritos' => array_sum(array_column($tutorias, 'inscritos')),
                'promedio_inscritos' => $dictadas === [] ? null : round(array_sum(array_column($dictadas, 'inscritos')) / count($dictadas), 1),
                'ocupacion_promedio' => $ocupaciones === [] ? null : (int) round(array_sum($ocupaciones) / count($ocupaciones)),
                'confirmados' => array_sum(array_column($tutorias, 'confirmados')),
                'cancelaciones' => array_sum(array_column($tutorias, 'cancelaciones')),
                'calificaciones' => $calificaciones,
                'promedio_calificacion' => $calificaciones === 0 ? null : round($sumaPuntos / $calificaciones, 1),
            ],
            'mejor_tutoria' => $this->best($tutorias),
            'tutorias' => $tutorias,
        ];
    }

    /**
     * Mejor tutoría: mayor promedio de calificación (desempata la cantidad de
     * calificaciones y luego los inscritos). Sin calificaciones: la de más inscritos.
     *
     * @param  list<array<string, mixed>>  $tutorias
     */
    private function best(array $tutorias): ?array
    {
        $calificadas = array_values(array_filter($tutorias, fn ($c) => $c['calificaciones'] > 0));
        if ($calificadas !== []) {
            usort($calificadas, fn ($a, $b) => [$b['promedio'], $b['calificaciones'], $b['inscritos']] <=> [$a['promedio'], $a['calificaciones'], $a['inscritos']]);

            return ['tutoria' => $calificadas[0], 'criterio' => 'CALIFICACION'];
        }

        $conInscritos = array_values(array_filter($tutorias, fn ($c) => $c['inscritos'] > 0));
        if ($conInscritos !== []) {
            usort($conInscritos, fn ($a, $b) => $b['inscritos'] <=> $a['inscritos']);

            return ['tutoria' => $conInscritos[0], 'criterio' => 'INSCRITOS'];
        }

        return null;
    }
}
