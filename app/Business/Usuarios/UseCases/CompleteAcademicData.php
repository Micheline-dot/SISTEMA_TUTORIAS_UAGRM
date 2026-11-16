<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Shared\Contracts\TransactionManager;
use App\Business\Usuarios\Contracts\FacultyRepository;
use App\Business\Usuarios\Contracts\UserProfileRepository;
use App\Business\Usuarios\DTO\CompleteAcademicDataInput;
use App\Business\Usuarios\Exceptions\AcademicDataAlreadyCompleted;
use App\Business\Usuarios\Exceptions\InvalidProfileData;
use App\Business\Usuarios\Exceptions\ProfileNotFound;

/**
 * CU02: registra UNA SOLA VEZ la facultad y el tipo de registro con sus datos:
 *  - ESTUDIANTE: registro universitario.
 *  - AUXILIAR:   registro universitario + registro de auxiliar.
 *  - DOCENTE:    código de docente.
 * Es información referencial para validar la solicitud de tutor (CU03/CU04).
 */
final class CompleteAcademicData
{
    public const TYPES = ['ESTUDIANTE', 'AUXILIAR', 'DOCENTE'];

    public const STUDENT_REGISTRY_PATTERN = '/^[0-9]{6,15}$/';

    public const CODE_PATTERN = '/^[A-Z0-9-]{3,30}$/';

    public function __construct(
        private readonly UserProfileRepository $profiles,
        private readonly FacultyRepository $faculties,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function execute(CompleteAcademicDataInput $input): void
    {
        $profile = $this->profiles->find($input->userId)
            ?? throw new ProfileNotFound('The user profile does not exist.');

        if ($profile->isAdministrator()) {
            throw new InvalidProfileData('Administrator accounts do not register academic data.');
        }

        if ($profile->academicDataCompleted()) {
            throw new AcademicDataAlreadyCompleted('Academic data can only be registered once.');
        }

        $faculty = trim($input->faculty);
        $type = strtoupper(trim($input->registrationType));

        if (! $this->faculties->exists($faculty)) {
            throw new FacultyNotFound('The selected faculty is not in the validated catalog.');
        }

        if (! in_array($type, self::TYPES, true)) {
            throw new InvalidProfileData('The registration type is invalid.');
        }

        $student = $this->clean($input->studentRegistry);
        $assistant = $this->clean($input->assistantRegistry);
        $teacher = $this->clean($input->teacherCode);

        // Solo se guardan los datos que corresponden al tipo elegido.
        [$student, $assistant, $teacher] = match ($type) {
            'ESTUDIANTE' => [
                $this->required($student, self::STUDENT_REGISTRY_PATTERN, 'student registry'),
                null,
                null,
            ],
            'AUXILIAR' => [
                $this->required($student, self::STUDENT_REGISTRY_PATTERN, 'student registry'),
                $this->required($assistant, self::CODE_PATTERN, 'assistant registry'),
                null,
            ],
            'DOCENTE' => [
                null,
                null,
                $this->required($teacher, self::CODE_PATTERN, 'teacher code'),
            ],
        };

        $saved = $this->transactions->run(fn (): bool => $this->profiles->completeAcademicData(
            $input->userId, $faculty, $type, $student, $assistant, $teacher,
        ));

        if (! $saved) {
            throw new AcademicDataAlreadyCompleted('Academic data can only be registered once.');
        }
    }

    private function clean(?string $value): ?string
    {
        $value = $value === null ? null : strtoupper(trim($value));

        return $value === '' ? null : $value;
    }

    private function required(?string $value, string $pattern, string $label): string
    {
        if ($value === null) {
            throw new InvalidProfileData("The {$label} is required.");
        }

        if (preg_match($pattern, $value) !== 1) {
            throw new InvalidProfileData("The {$label} format is invalid.");
        }

        return $value;
    }
}
