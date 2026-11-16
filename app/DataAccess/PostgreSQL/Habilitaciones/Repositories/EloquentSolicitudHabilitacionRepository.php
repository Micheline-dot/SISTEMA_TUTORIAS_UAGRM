<?php

namespace App\DataAccess\PostgreSQL\Habilitaciones\Repositories;

use App\Business\Habilitaciones\Contracts\SolicitudHabilitacionRepository;
use App\DataAccess\PostgreSQL\Habilitaciones\Models\SolicitudHabilitacionTutor;

final class EloquentSolicitudHabilitacionRepository implements SolicitudHabilitacionRepository
{
    public function crear(array $datos): int
    {
        $solicitud = SolicitudHabilitacionTutor::query()->create($datos);

        return (int) $solicitud->id_solicitud;
    }

    public function buscarPorId(int $id): ?array
    {
        $solicitud = SolicitudHabilitacionTutor::query()->find($id);

        return $solicitud?->toArray();
    }

    public function buscarPendientes(): array
    {
        return SolicitudHabilitacionTutor::query()
            ->with('solicitante:id_usuario,nombre_completo')
            ->where('estado', 'PENDIENTE')
            ->orderBy('created_at')
            ->get()
            ->map(function ($solicitud) {
                $datos = $solicitud->toArray();

                $datos['nombre_solicitante'] =
                    $solicitud->solicitante?->nombre_completo
                    ?? 'Usuario no identificado';

                return $datos;
            })
            ->toArray();
    }

    public function buscarPorUsuario(int $idUsuario): array
    {
        return SolicitudHabilitacionTutor::query()
            ->where('id_usuario', $idUsuario)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }

    public function actualizarRevision(
        int $idSolicitud,
        int $idAdministrador,
        string $estado,
        ?string $observacion
    ): bool {
        if (!in_array($estado, ['APROBADA', 'RECHAZADA'], true)) {
            return false;
        }

        $solicitud = SolicitudHabilitacionTutor::query()
            ->where('id_solicitud', $idSolicitud)
            ->where('estado', 'PENDIENTE')
            ->lockForUpdate()
            ->first();

        if ($solicitud === null) {
            return false;
        }

        $solicitud->estado = $estado;
        $solicitud->id_administrador_revisor = $idAdministrador;
        $solicitud->observacion = $observacion;
        $solicitud->fecha_revision = now();
        $solicitud->save();

        return true;
    }
}