<?php

namespace Database\Seeders;

use App\Business\Usuarios\DTO\CreateAdministratorInput;
use App\Business\Usuarios\UseCases\CreateInitialAdministrator;
use Illuminate\Database\Seeder;

/**
 * Crea el administrador inicial a partir de las variables ADMIN_* del .env.
 * - Si ADMIN_EMAIL o ADMIN_PASSWORD están vacías, no hace nada.
 * - Si ya existe algún administrador, no hace nada (se puede ejecutar varias veces).
 */
final class InitialAdminSeeder extends Seeder
{
    public function run(CreateInitialAdministrator $createInitialAdministrator): void
    {
        $data = config('app.initial_admin');

        if (blank($data['email'] ?? null) || blank($data['password'] ?? null)) {
            $this->command?->warn(
                'Administrador inicial omitido: define ADMIN_EMAIL y ADMIN_PASSWORD en el .env.'
            );

            return;
        }

        $id = $createInitialAdministrator->execute(new CreateAdministratorInput(
            (string) ($data['first_names'] ?: 'Administrador'),
            (string) ($data['last_names'] ?: 'del Sistema'),
            (string) $data['email'],
            (string) $data['password'],
            (string) ($data['phone'] ?: '00000000'),
            (string) ($data['position'] ?: 'Administrador del sistema'),
        ));

        $this->command?->info(
            $id === null
                ? 'Ya existe un administrador; no se creó uno nuevo.'
                : "Administrador inicial creado ({$data['email']}). Borra ADMIN_PASSWORD del .env."
        );
    }
}
