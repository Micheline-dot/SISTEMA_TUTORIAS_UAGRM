<?php

namespace App\Business\Administracion\UseCases;

use App\Business\Administracion\Contracts\AdminCourseRepository;

final class ListAdminCourses
{
    public function __construct(
        private readonly AdminCourseRepository $repository
    ) {
    }

    public function execute(string $search = '', string $status = ''): array
    {
        $allowedStatuses = [
            '',
            'BORRADOR',
            'PUBLICADO',
            'CANCELADO',
            'FINALIZADO',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        return $this->repository->listar(
            trim($search),
            $status
        );
    }
}