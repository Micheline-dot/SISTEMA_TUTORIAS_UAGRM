<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Shared\Contracts\TransactionManager;
use App\Business\Usuarios\Contracts\AcademicProfileRepository;
use App\Business\Usuarios\Contracts\FacultyRepository;
use App\Business\Usuarios\Contracts\PasswordHasher;
use App\Business\Usuarios\Contracts\RoleAssignmentRepository;
use App\Business\Usuarios\Contracts\UserRepository;
use App\Business\Usuarios\DTO\RegisterUserInput;

final class RegisterUser
{
    private const INITIAL_ROLE = 'ESTUDIANTE';

    public function __construct(
        private readonly UserRepository $users,
        private readonly AcademicProfileRepository $profiles,
        private readonly FacultyRepository $faculties,
        private readonly RoleAssignmentRepository $roles,
        private readonly PasswordHasher $passwords,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function execute(RegisterUserInput $input): string
    {
        $this->assertValid($input);
        $passwordHash = $this->passwords->hash($input->plainTextPassword);

        return $this->transactions->run(function () use ($input, $passwordHash): string {
            if (! $this->faculties->exists($input->facultyId)) {
                throw new FacultyNotFound('The selected faculty is not in the validated catalog.');
            }

            $userId = $this->users->create(
                $input->fullName,
                $input->email,
                $passwordHash,
                $input->phone,
                true,
            );

            $this->profiles->create($userId, $input->facultyId);
            $this->roles->assign($userId, self::INITIAL_ROLE);

            return $userId;
        });
    }

    private function assertValid(RegisterUserInput $input): void
    {
        foreach (['fullName', 'email', 'plainTextPassword', 'facultyId', 'phone'] as $field) {
            if (trim($input->{$field}) === '') {
                throw new InvalidRegistrationData("The {$field} field is required.");
            }
        }

        if (filter_var($input->email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidRegistrationData('The email address is invalid.');
        }

        if (! ctype_digit($input->facultyId) || (int) $input->facultyId < 1) {
            throw new InvalidRegistrationData('The selected faculty is invalid.');
        }
    }
}
