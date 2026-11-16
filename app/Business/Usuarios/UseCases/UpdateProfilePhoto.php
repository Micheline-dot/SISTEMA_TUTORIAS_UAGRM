<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Usuarios\Contracts\ProfilePhotoStorage;
use App\Business\Usuarios\Contracts\UserProfileRepository;
use App\Business\Usuarios\Exceptions\InvalidProfileData;
use App\Business\Usuarios\Exceptions\ProfileNotFound;

/** CU02: cambiar o quitar la foto de perfil (en cualquier momento). */
final class UpdateProfilePhoto
{
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public const MAX_KILOBYTES = 2048;

    public function __construct(
        private readonly UserProfileRepository $profiles,
        private readonly ProfilePhotoStorage $photos,
    ) {
    }

    public function execute(string $userId, string $temporaryPath, string $extension): void
    {
        $extension = strtolower($extension);

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidProfileData('The photo must be a JPG, PNG or WEBP image.');
        }

        $profile = $this->profiles->find($userId)
            ?? throw new ProfileNotFound('The user profile does not exist.');

        $newPath = $this->photos->store($userId, $temporaryPath, $extension);

        try {
            $this->profiles->updatePhotoPath($userId, $newPath);
        } catch (\Throwable $e) {
            $this->photos->delete($newPath);
            throw $e;
        }

        if ($profile->photoPath !== null) {
            $this->photos->delete($profile->photoPath);
        }
    }

    public function remove(string $userId): void
    {
        $profile = $this->profiles->find($userId)
            ?? throw new ProfileNotFound('The user profile does not exist.');

        if ($profile->photoPath === null) {
            return;
        }

        $this->profiles->updatePhotoPath($userId, null);
        $this->photos->delete($profile->photoPath);
    }
}
