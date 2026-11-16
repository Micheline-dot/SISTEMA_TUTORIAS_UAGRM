<?php

namespace App\Business\Administracion\UseCases;

use App\Business\Administracion\Contracts\AdminCourseRepository;

final class GetAdminCourseDetails
{
    public function __construct(
        private readonly AdminCourseRepository $repository
    ) {
    }

    public function execute(string $courseId): ?array
    {
        $tutoria = $this->repository->buscarDetalle($courseId);

        if ($tutoria === null) {
            return null;
        }

        $tutoria['inscritos'] = $this->repository->listarInscritos(
            $courseId
        );

        return $tutoria;
    }
}