<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Shared\Contracts\TransactionManager;
use App\Business\Usuarios\Contracts\PasswordHasher;
use App\Business\Usuarios\Contracts\RoleAssignmentRepository;
use App\Business\Usuarios\Contracts\UserRepository;
use App\Business\Usuarios\DTO\CreateAdministratorInput;

/**
 * Crea una cuenta de administrador independiente: Usuario con
 * tipo_registro ADMINISTRADOR y su especialización Administrador
 * (sin Estudiante ni Tutor). Solo se invoca desde consola/seeder.
 */
final class CreateAdministrator
{
    private const REGISTRATION_TYPE = 'ADMINISTRADOR';

    private const MIN_PASSWORD_LENGTH = 8;

    public function __construct(
        private readonly UserRepository $users,
        private readonly RoleAssignmentRepository $roles,
        private readonly PasswordHasher $passwords,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function execute(CreateAdministratorInput $input): string
    {
        $this->assertValid($input);
        $passwordHash = $this->passwords->hash($input->plainTextPassword);

        return $this->transactions->run(function () use ($input, $passwordHash): string {
            $userId = $this->users->create(
                trim($input->firstNames),
                trim($input->lastNames),
                trim($input->email),
                $passwordHash,
                trim($input->phone),
                null,
                self::REGISTRATION_TYPE,
                true,
            );

            $this->roles->assignAdministrator($userId, trim($input->position));

            return $userId;
        });
    }

    private function assertValid(CreateAdministratorInput $input): void
    {
        foreach (['firstNames', 'lastNames', 'email', 'plainTextPassword', 'phone', 'position'] as $field) {
            if (trim($input->{$field}) === '') {
                throw new InvalidRegistrationData("The {$field} field is required.");
            }
        }

        if (filter_var(trim($input->email), FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidRegistrationData('The email address is invalid.');
        }

        if (mb_strlen($input->plainTextPassword) < self::MIN_PASSWORD_LENGTH) {
            throw new InvalidRegistrationData(
                'The password must have at least '.self::MIN_PASSWORD_LENGTH.' characters.'
            );
        }
    }
}
