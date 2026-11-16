<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Usuarios\Contracts\UserProfileRepository;
use App\Business\Usuarios\DTO\UserProfile;
use App\Business\Usuarios\Exceptions\ProfileNotFound;

/** CU02: consultar el propio perfil. */
final class GetUserProfile
{
    public function __construct(private readonly UserProfileRepository $profiles)
    {
    }

    public function execute(string $userId): UserProfile
    {
        return $this->profiles->find($userId)
            ?? throw new ProfileNotFound('The user profile does not exist.');
    }
}
