<?php

namespace App\Presentation\Http;

use Symfony\Component\HttpFoundation\StreamedResponse;

/** Descarga CSV compatible con Excel (UTF-8 con BOM, separador ";"). */
final class CsvResponse
{
    /**
     * @param  list<list<scalar|null>>  $rows  Filas; una fila vacía separa secciones.
     */
    public static function make(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($v) => self::safe($v), $row), ';', '"', '');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Evita inyección de fórmulas al abrir el archivo en una hoja de cálculo. */
    private static function safe(mixed $value): string
    {
        $value = $value === null ? '' : (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) && ! is_numeric($value)
            ? "'".$value
            : $value;
    }
}
