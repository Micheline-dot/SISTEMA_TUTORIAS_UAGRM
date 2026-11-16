<?php

namespace App\DataAccess\PostgreSQL\Notificaciones\Repositories;

use App\Business\Notificaciones\Contracts\NotificacionRepository;
use App\Business\Notificaciones\DTO\Notificacion;
use Illuminate\Support\Facades\DB;

/**
 * notificacion (mensaje, fecha, estado = leída, titulo, enlace) +
 * notificacion_usuario (destinatario). Una notificación por destinatario.
 */
final class EloquentNotificacionRepository implements NotificacionRepository
{
    public function crearParaUsuarios(array $idsUsuario, string $titulo, string $mensaje, ?string $enlace): void
    {
        DB::transaction(function () use ($idsUsuario, $titulo, $mensaje, $enlace): void {
            foreach ($idsUsuario as $idUsuario) {
                $id = DB::table('notificacion')->insertGetId([
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'enlace' => $enlace,
                    'estado' => false,
                ], 'id_notificacion');

                DB::table('notificacion_usuario')->insert([
                    'id_usuario' => (int) $idUsuario,
                    'id_notificacion' => $id,
                ]);
            }
        });
    }

    public function listarDeUsuario(string $idUsuario, bool $soloNoLeidas, int $limite): array
    {
        return $this->deUsuario($idUsuario)
            ->when($soloNoLeidas, fn ($q) => $q->where('n.estado', false))
            ->orderByDesc('n.id_notificacion')
            ->limit($limite)
            ->get(['n.id_notificacion', 'n.titulo', 'n.mensaje', 'n.enlace', 'n.fecha', 'n.estado'])
            ->map(static fn (object $n): Notificacion => new Notificacion(
                (string) $n->id_notificacion,
                $n->titulo ?? 'Notificación',
                $n->mensaje,
                $n->enlace,
                date('d/m/Y', strtotime((string) $n->fecha)),
                (bool) $n->estado,
            ))
            ->all();
    }

    public function contarNoLeidas(string $idUsuario): int
    {
        return $this->deUsuario($idUsuario)->where('n.estado', false)->count();
    }

    public function marcarLeida(string $idUsuario, string $idNotificacion): bool
    {
        if (! $this->deUsuario($idUsuario)->where('n.id_notificacion', (int) $idNotificacion)->exists()) {
            return false;
        }

        DB::table('notificacion')->where('id_notificacion', (int) $idNotificacion)->update(['estado' => true]);

        return true;
    }

    public function marcarTodasLeidas(string $idUsuario): int
    {
        return DB::table('notificacion')
            ->where('estado', false)
            ->whereIn('id_notificacion', DB::table('notificacion_usuario')
                ->where('id_usuario', (int) $idUsuario)
                ->select('id_notificacion'))
            ->update(['estado' => true]);
    }

    public function enlaceDe(string $idUsuario, string $idNotificacion): ?string
    {
        return $this->deUsuario($idUsuario)
            ->where('n.id_notificacion', (int) $idNotificacion)
            ->value('n.enlace');
    }

    private function deUsuario(string $idUsuario)
    {
        return DB::table('notificacion as n')
            ->join('notificacion_usuario as nu', 'nu.id_notificacion', '=', 'n.id_notificacion')
            ->where('nu.id_usuario', (int) $idUsuario);
    }
}
