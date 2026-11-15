<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['nombre' => 'ESTUDIANTE'],
            ['nombre' => 'TUTOR'],
            ['nombre' => 'ADMINISTRADOR'],
        ]);
    }
}
