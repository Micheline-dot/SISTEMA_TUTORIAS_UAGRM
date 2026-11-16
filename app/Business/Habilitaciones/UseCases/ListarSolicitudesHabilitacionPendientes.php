<?php

namespace App\Business\Habilitaciones\UseCases;

use App\Business\Habilitaciones\Contracts\SolicitudHabilitacionRepository;

final class ListarSolicitudesHabilitacionPendientes
{
    public function __construct(
        private SolicitudHabilitacionRepository $repository
    ) {
    }

    public function ejecutar(): array
    {
        return $this->repository->buscarPendientes();
    }
}