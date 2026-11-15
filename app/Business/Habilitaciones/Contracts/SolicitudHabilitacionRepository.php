<?php

namespace App\Business\Habilitaciones\Contracts;

interface SolicitudHabilitacionRepository
{
    public function crear(array $datos): int;

    public function buscarPorId(int $id): ?array;

    public function buscarPendientes(): array;

    public function buscarPorUsuario(int $idUsuario): array;

    public function actualizarRevision(
        int $idSolicitud,
        int $idAdministrador,
        string $estado,
        ?string $observacion
    ): bool;
}