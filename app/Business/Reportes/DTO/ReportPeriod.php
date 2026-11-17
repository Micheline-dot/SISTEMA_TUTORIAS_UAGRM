<?php

namespace App\Business\Reportes\DTO;

use App\Business\Reportes\Exceptions\InvalidReportPeriod;
use DateTimeImmutable;

/**
 * Periodo opcional de un reporte (fechas inclusivas, formato Y-m-d).
 * Filtra inscripciones (inscripcion.fecha) y calificaciones (calificacion_resena.fecha).
 */
final class ReportPeriod
{
    private function __construct(
        public readonly ?string $from,
        public readonly ?string $to,
    ) {
    }

    public static function all(): self
    {
        return new self(null, null);
    }

    public static function fromInput(?string $from, ?string $to): self
    {
        $from = self::parse($from, 'inicial');
        $to = self::parse($to, 'final');

        if ($from !== null && $to !== null && $from > $to) {
            throw new InvalidReportPeriod('La fecha inicial no puede ser posterior a la fecha final.');
        }

        return new self($from, $to);
    }

    public function isAll(): bool
    {
        return $this->from === null && $this->to === null;
    }

    private static function parse(?string $value, string $label): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new InvalidReportPeriod("La fecha {$label} no es válida.");
        }

        return $value;
    }
}
