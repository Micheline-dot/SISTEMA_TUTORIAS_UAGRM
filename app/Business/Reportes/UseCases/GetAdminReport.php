<?php

namespace App\Business\Reportes\UseCases;

use App\Business\Reportes\Contracts\ReportRepository;
use App\Business\Reportes\DTO\ReportPeriod;

/**
 * CU22 Generar reportes (administrador): totales del sistema, mejores tutores,
 * categoría y facultad más solicitadas, opcionalmente dentro de un periodo.
 */
final class GetAdminReport
{
    public const TOP_TUTORS = 5;

    /** Un tutor entra al ranking con al menos esta cantidad de calificaciones. */
    public const MIN_RATINGS = 1;

    public function __construct(private readonly ReportRepository $reports)
    {
    }

    /**
     * @return array{
     *   periodo: ReportPeriod,
     *   totales: array<string, mixed>,
     *   mejores_tutores: list<array<string, mixed>>,
     *   categorias: list<array<string, mixed>>,
     *   facultades: list<array<string, mixed>>,
     *   categoria_mas_solicitada: ?array<string, mixed>,
     *   facultad_mas_solicitada: ?array<string, mixed>,
     *   mejor_tutor: ?array<string, mixed>
     * }
     */
    public function execute(?ReportPeriod $period = null): array
    {
        $period ??= ReportPeriod::all();

        $tutores = $this->reports->topTutors($period, self::MIN_RATINGS, self::TOP_TUTORS);
        $categorias = $this->reports->demandByCategory($period);
        $facultades = $this->reports->demandByFaculty($period);

        return [
            'periodo' => $period,
            'totales' => $this->reports->adminTotals($period),
            'mejores_tutores' => $tutores,
            'categorias' => $categorias,
            'facultades' => $facultades,
            'categoria_mas_solicitada' => $this->leader($categorias),
            'facultad_mas_solicitada' => $this->leader($facultades),
            'mejor_tutor' => $tutores[0] ?? null,
        ];
    }

    /**
     * Primer elemento con inscripciones (las listas ya vienen ordenadas).
     *
     * @param  list<array{inscripciones: int}>  $rows
     */
    private function leader(array $rows): ?array
    {
        $first = $rows[0] ?? null;

        return $first !== null && $first['inscripciones'] > 0 ? $first : null;
    }
}
