@'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $faculties = [
            'Facultad de Ciencias Agrícolas',
            'Facultad de Ciencias Económicas, Administrativas y Financieras',
            'Facultad de Ciencias Contables, Auditoría, Sistemas de Control de Gestión y Finanzas',
            'Facultad de Ciencias del Hábitat, Diseño y Arte',
            'Facultad de Ciencias de la Salud Humana',
            'Facultad de Ciencias Farmacéuticas y Bioquímicas',
            'Facultad de Ciencias Veterinarias',
            'Facultad de Derecho, Ciencias Políticas y Sociales',
            'Facultad de Humanidades',
            'Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones',
            'Facultad de Ciencias Exactas y Tecnología',
            'Facultad Politécnica',
        ];

        foreach ($faculties as $faculty) {
            DB::table('facultades')->updateOrInsert(
                ['nombre' => $faculty],
                []
            );
        }
    }
}
'@ | Set-Content -Path .\database\seeders\FacultySeeder.php -Encoding utf8