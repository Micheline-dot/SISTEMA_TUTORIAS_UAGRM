<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Usuarios\Contracts\UserProfileRepository;
use App\Business\Usuarios\Exceptions\InvalidProfileData;
use App\Business\Usuarios\Exceptions\ProfileNotFound;

/**
 * CU02: datos editables en cualquier momento (nombres, apellidos, teléfono).
 * El correo no se modifica: es el identificador con el que se registró.
 */
final class UpdatePersonalData
{
    public const PHONE_PATTERN = '/^\+?[0-9]{7,15}$/';

    public function __construct(private readonly UserProfileRepository $profiles)
    {
    }

    public function execute(string $userId, string $firstNames, string $lastNames, ?string $phone): void
    {
        $firstNames = trim($firstNames);
        $lastNames = trim($lastNames);
        $phone = $phone === null ? null : str_replace([' ', '-'], '', trim($phone));
        $phone = $phone === '' ? null : $phone;

        if ($firstNames === '' || $lastNames === '') {
            throw new InvalidProfileData('Names are required.');
        }

        if (mb_strlen($firstNames) > 100 || mb_strlen($lastNames) > 100) {
            throw new InvalidProfileData('Names cannot exceed 100 characters.');
        }

        if ($phone !== null && preg_match(self::PHONE_PATTERN, $phone) !== 1) {
            throw new InvalidProfileData('The phone number is invalid.');
        }

        if ($this->profiles->find($userId) === null) {
            throw new ProfileNotFound('The user profile does not exist.');
        }

        $this->profiles->updatePersonalData($userId, $firstNames, $lastNames, $phone);
    }
}
