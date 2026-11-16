<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Usuarios\Contracts\ProfilePhotoStorage;
use App\Business\Usuarios\Contracts\UserProfileRepository;

/** Devuelve la ruta almacenada de la foto de un usuario, o null si no tiene. */
final class GetProfilePhoto
{
    public function __construct(
        private readonly UserProfileRepository $profiles,
        private readonly ProfilePhotoStorage $photos,
    ) {
    }

    public function execute(string $userId): ?string
    {
        $path = $this->profiles->find($userId)?->photoPath;

        return $path !== null && $this->photos->exists($path) ? $path : null;
    }
}
