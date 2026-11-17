<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Los archivos fuente deben ser UTF-8 SIN BOM.
 *
 * Un BOM al inicio de una vista Livewire impide que Livewire encuentre el
 * elemento raíz del componente: el estado se pega a otro elemento y ningún
 * botón (wire:click / wire:submit) responde en el navegador.
 */
final class SourceEncodingTest extends TestCase
{
    public function test_source_files_do_not_start_with_utf8_bom(): void
    {
        $root = dirname(__DIR__, 2);
        $withBom = [];

        foreach (['app', 'resources/views', 'routes', 'config', 'database'] as $dir) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/$dir", RecursiveDirectoryIterator::SKIP_DOTS));

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                if (file_get_contents($file->getPathname(), false, null, 0, 3) === "\xEF\xBB\xBF") {
                    $withBom[] = substr($file->getPathname(), strlen($root) + 1);
                }
            }
        }

        self::assertSame([], $withBom, 'Guarda estos archivos como UTF-8 sin BOM: '.implode(', ', $withBom));
    }
}
