<?php

namespace App\DataAccess\PostgreSQL\Habilitaciones\Repositories;

use App\Business\Habilitaciones\Contracts\SolicitudHabilitacionRepository;
use App\DataAccess\PostgreSQL\Habilitaciones\Models\DocumentoSolicitud;
use App\DataAccess\PostgreSQL\Habilitaciones\Models\SolicitudHabilitacion;
use App\Business\Habilitaciones\Exceptions\SolicitudPendienteExistente;
use App\DataAccess\PostgreSQL\Users\Repositories\UniqueViolation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Traduce solicitud_habilitacion + documento_solicitud a la estructura que
 * usa la lógica de negocio (estado PENDIENTE/APROBADA/RECHAZADA, un índice
 * por tipo de documento con su ruta, tipo_personal, codigo_doc, ...).
 */
final class EloquentSolicitudHabilitacionRepository implements SolicitudHabilitacionRepository
{
    private const DOCUMENTOS = [
        'curriculum',
        'documento_respaldo',
        'carta_solicitud',
        'registro_academico',
        'historial_academico',
    ];

    private const ESTADOS = [
        SolicitudHabilitacion::PENDIENTE => 'PENDIENTE',
        SolicitudHabilitacion::APROBADA => 'APROBADA',
        SolicitudHabilitacion::RECHAZADA => 'RECHAZADA',
    ];

    public function crear(array $datos): int
    {
        try {
            return $this->insertar($datos);
        } catch (QueryException $exception) {
            if (UniqueViolation::matches($exception, 'solicitud_habilitacion_pendiente_unique')) {
                throw new SolicitudPendienteExistente(previous: $exception);
            }

            throw $exception;
        }
    }

    private function insertar(array $datos): int
    {
        return DB::transaction(function () use ($datos): int {
            $tipo = (string) $datos['tipo_personal'];

            $solicitud = SolicitudHabilitacion::query()->create([
                'estado' => SolicitudHabilitacion::PENDIENTE,
                'id_estudiante' => (int) $datos['id_usuario'],
                'tipo_academico' => $tipo,
                'codigo' => match ($tipo) {
                    'DOCENTE' => $datos['codigo_doc'] ?? null,
                    'AUXILIAR' => $datos['codigo_auxiliar'] ?? null,
                    default => null,
                },
            ]);

            foreach (self::DOCUMENTOS as $nombre) {
                if (! empty($datos[$nombre])) {
                    DocumentoSolicitud::query()->create([
                        'id_solicitud' => $solicitud->id_solicitud,
                        'nombre' => $nombre,
                        'ruta' => $datos[$nombre],
                    ]);
                }
            }

            return (int) $solicitud->id_solicitud;
        });
    }

    public function buscarPorId(int $id): ?array
    {
        $solicitud = SolicitudHabilitacion::query()
            ->with(['documentos', 'estudiante:id_usuario,nombres,apellidos,correo,facultad', 'estudiante.estudiante:id_estudiante,registro'])
            ->find($id);

        return $solicitud === null ? null : $this->toArray($solicitud);
    }

    public function buscarPendientes(): array
    {
        return SolicitudHabilitacion::query()
            ->with(['documentos', 'estudiante:id_usuario,nombres,apellidos,correo,facultad', 'estudiante.estudiante:id_estudiante,registro'])
            ->where('estado', SolicitudHabilitacion::PENDIENTE)
            ->orderBy('fecha')
            ->orderBy('id_solicitud')
            ->get()
            ->map(fn (SolicitudHabilitacion $solicitud): array => $this->toArray($solicitud))
            ->all();
    }

    public function buscarPorUsuario(int $idUsuario): array
    {
        return SolicitudHabilitacion::query()
            ->with('documentos')
            ->where('id_estudiante', $idUsuario)
            ->orderByDesc('fecha')
            ->orderByDesc('id_solicitud')
            ->get()
            ->map(fn (SolicitudHabilitacion $solicitud): array => $this->toArray($solicitud))
            ->all();
    }

    public function actualizarRevision(
        int $idSolicitud,
        int $idAdministrador,
        string $estado,
        ?string $observacion
    ): bool {
        $nuevoEstado = match ($estado) {
            'APROBADA' => SolicitudHabilitacion::APROBADA,
            'RECHAZADA' => SolicitudHabilitacion::RECHAZADA,
            default => null,
        };

        if ($nuevoEstado === null) {
            return false;
        }

        $solicitud = SolicitudHabilitacion::query()
            ->where('id_solicitud', $idSolicitud)
            ->where('estado', SolicitudHabilitacion::PENDIENTE)
            ->lockForUpdate()
            ->first();

        if ($solicitud === null) {
            return false;
        }

        $solicitud->estado = $nuevoEstado;
        $solicitud->id_administrador = $idAdministrador;
        $solicitud->comentario_rechazo = $observacion;
        $solicitud->save();

        return true;
    }

    private function toArray(SolicitudHabilitacion $solicitud): array
    {
        $datos = [
            'id_solicitud' => (int) $solicitud->id_solicitud,
            'id_usuario' => (int) $solicitud->id_estudiante,
            'id_estudiante' => (int) $solicitud->id_estudiante,
            'id_administrador' => $solicitud->id_administrador,
            'id_administrador_revisor' => $solicitud->id_administrador,
            'tipo_personal' => $solicitud->tipo_academico,
            'codigo_doc' => $solicitud->tipo_academico === 'DOCENTE' ? $solicitud->codigo : null,
            'codigo_auxiliar' => $solicitud->tipo_academico === 'AUXILIAR' ? $solicitud->codigo : null,
            'estado' => self::ESTADOS[$solicitud->estado] ?? $solicitud->estado,
            'observacion' => $solicitud->comentario_rechazo,
            'comentario_rechazo' => $solicitud->comentario_rechazo,
            'fecha' => $solicitud->fecha?->toDateString(),
            'created_at' => $solicitud->fecha?->format('d/m/Y'),
        ];

        foreach (self::DOCUMENTOS as $nombre) {
            $datos[$nombre] = null;
        }

        foreach ($solicitud->documentos as $documento) {
            $datos[$documento->nombre] = $documento->ruta;
        }

        if ($solicitud->relationLoaded('estudiante')) {
            $datos['nombre_solicitante'] = $solicitud->estudiante?->nombre_completo
                ?: 'Usuario no identificado';
            // Datos del perfil (CU02) para validar la solicitud.
            $datos['correo_solicitante'] = $solicitud->estudiante?->correo;
            $datos['facultad_solicitante'] = $solicitud->estudiante?->facultad;
            $datos['registro_universitario'] = $solicitud->estudiante?->estudiante?->registro;
        }

        return $datos;
    }
}
