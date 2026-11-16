<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * CU21: categorías iniciales para clasificar las tutorías. Idempotente:
 * solo inserta las que no existen; el administrador puede gestionarlas después.
 */
class CategorySeeder extends Seeder
{
    public const CATEGORIAS = [
        'Matemáticas',
        'Física',
        'Química',
        'Biología',
        'Programación',
        'Bases de datos',
        'Redes y sistemas',
        'Economía y finanzas',
        'Contabilidad',
        'Derecho',
        'Idiomas',
        'Ciencias de la salud',
        'Ingeniería',
        'Metodología de la investigación',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIAS as $nombre) {
            DB::table('categoria')->insertOrIgnore(['nombre' => $nombre]);
        }
    }
}
