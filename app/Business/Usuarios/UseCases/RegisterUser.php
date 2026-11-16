<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Shared\Contracts\TransactionManager;
use App\Business\Usuarios\Contracts\PasswordHasher;
use App\Business\Usuarios\Contracts\RoleAssignmentRepository;
use App\Business\Usuarios\Contracts\UserRepository;
use App\Business\Usuarios\DTO\RegisterUserInput;

/**
 * CU01 Registro público: crea el Usuario (tipo_registro ESTUDIANTE) y su
 * especialización Estudiante sin registro universitario, que se completa
 * luego en "Mi perfil". Solo se aceptan correos @gmail.com.
 */
final class RegisterUser
{
    private const REGISTRATION_TYPE = 'ESTUDIANTE';

    public const ALLOWED_EMAIL_DOMAIN = 'gmail.com';

    public const MIN_PASSWORD_LENGTH = 8;

    public function __construct(
        private readonly UserRepository $users,
        private readonly RoleAssignmentRepository $roles,
        private readonly PasswordHasher $passwords,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function execute(RegisterUserInput $input): string
    {
        $email = self::normalizeEmail($input->email);

        $this->assertValid($input, $email);

        $passwordHash = $this->passwords->hash($input->plainTextPassword);

        return $this->transactions->run(function () use ($input, $email, $passwordHash): string {
            $userId = $this->users->create(
                trim($input->firstNames),
                trim($input->lastNames),
                $email,
                $passwordHash,
                null,
                null,
                self::REGISTRATION_TYPE,
                true,
            );

            $this->roles->assignStudent($userId, null);

            return $userId;
        });
    }

    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public static function isAllowedEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && str_ends_with($email, '@'.self::ALLOWED_EMAIL_DOMAIN);
    }

    private function assertValid(RegisterUserInput $input, string $email): void
    {
        foreach (['firstNames', 'lastNames', 'email', 'plainTextPassword'] as $field) {
            if (trim($input->{$field}) === '') {
                throw new InvalidRegistrationData("The {$field} field is required.");
            }
        }

        if (mb_strlen(trim($input->firstNames)) > 100 || mb_strlen(trim($input->lastNames)) > 100) {
            throw new InvalidRegistrationData('Names cannot exceed 100 characters.');
        }

        if (! self::isAllowedEmail($email) || mb_strlen($email) > 150) {
            throw new InvalidRegistrationData('Only @gmail.com email addresses are accepted.');
        }

        if (mb_strlen($input->plainTextPassword) < self::MIN_PASSWORD_LENGTH) {
            throw new InvalidRegistrationData('The password must be at least 8 characters.');
        }
    }
}
