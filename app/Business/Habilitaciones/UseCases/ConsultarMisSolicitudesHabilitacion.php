<?php

namespace App\Business\Habilitaciones\UseCases;

use App\Business\Habilitaciones\Contracts\SolicitudHabilitacionRepository;

/**
 * CU03: el estudiante consulta el estado de sus solicitudes
 * (la más reciente primero): PENDIENTE, APROBADA o RECHAZADA con su motivo.
 */
final class ConsultarMisSolicitudesHabilitacion
{
    public function __construct(private SolicitudHabilitacionRepository $repository)
    {
    }

    /** @return list<array{id_solicitud:int, estado:string, fecha:?string, tipo_personal:string, comentario_rechazo:?string}> */
    public function ejecutar(int $idUsuario): array
    {
        return array_map(static fn (array $s): array => [
            'id_solicitud' => $s['id_solicitud'],
            'estado' => $s['estado'],
            'fecha' => $s['created_at'],
            'tipo_personal' => $s['tipo_personal'],
            'comentario_rechazo' => $s['comentario_rechazo'],
        ], $this->repository->buscarPorUsuario($idUsuario));
    }

    /** @param list<array{estado:string}> $solicitudes */
    public static function tieneEstado(array $solicitudes, string $estado): bool
    {
        foreach ($solicitudes as $s) {
            if ($s['estado'] === $estado) {
                return true;
            }
        }

        return false;
    }
}
