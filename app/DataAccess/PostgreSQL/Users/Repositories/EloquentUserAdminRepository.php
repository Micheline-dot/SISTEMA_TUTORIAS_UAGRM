<?php

namespace App\DataAccess\PostgreSQL\Users\Repositories;

use App\Business\Usuarios\Contracts\UserAdminRepository;
use App\Business\Usuarios\DTO\UserSummary;
use App\DataAccess\PostgreSQL\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EloquentUserAdminRepository implements UserAdminRepository
{
    public function search(string $text, string $role, string $status, int $page, int $perPage): array
    {
        $query = User::query()->with(['estudiante', 'tutor', 'administrador']);

        if ($text !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($text)).'%';
            $query->where(function (Builder $q) use ($like): void {
                $q->whereRaw("LOWER(nombres || ' ' || apellidos) LIKE ?", [$like])
                    ->orWhereRaw('LOWER(correo) LIKE ?', [$like])
                    ->orWhereHas('estudiante', fn (Builder $e) => $e->whereRaw('LOWER(registro) LIKE ?', [$like]));
            });
        }

        match ($role) {
            'ESTUDIANTE' => $query->whereHas('estudiante')->whereDoesntHave('tutor'),
            'TUTOR' => $query->whereHas('tutor'),
            'ADMINISTRADOR' => $query->whereHas('administrador'),
            default => null,
        };

        if ($status !== '') {
            $query->where('activo', $status === 'ACTIVO');
        }

        $total = (clone $query)->count();

        $items = $query
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->orderBy('id_usuario')
            ->forPage($page, $perPage)
            ->get()
            ->map(fn (User $u): UserSummary => $this->toSummary($u))
            ->all();

        return ['items' => $items, 'total' => $total];
    }

    public function find(string $userId): ?UserSummary
    {
        $user = User::query()
            ->with(['estudiante', 'tutor', 'administrador'])
            ->find(PostgreSqlIntegerId::toInteger($userId));

        return $user === null ? null : $this->toSummary($user);
    }

    public function setActive(string $userId, bool $active): void
    {
        User::query()->whereKey(PostgreSqlIntegerId::toInteger($userId))->update(['activo' => $active]);
    }

    public function countActiveAdministrators(): int
    {
        return User::query()->where('activo', true)->whereHas('administrador')->count();
    }

    public function publishedCourseIdsOfTutor(string $userId): array
    {
        return DB::table('curso')
            ->where('id_tutor', PostgreSqlIntegerId::toInteger($userId))
            ->where('estado', 'PUBLICADO')
            ->orderBy('id_curso')
            ->pluck('id_curso')
            ->map(static fn ($id): string => (string) $id)
            ->all();
    }

    private function toSummary(User $u): UserSummary
    {
        $roles = [];
        if ($u->estudiante !== null) {
            $roles[] = User::ROL_ESTUDIANTE;
        }
        if ($u->tutor !== null) {
            $roles[] = User::ROL_TUTOR;
        }
        if ($u->administrador !== null) {
            $roles[] = User::ROL_ADMINISTRADOR;
        }

        return new UserSummary(
            PostgreSqlIntegerId::toString($u->getKey()),
            $u->nombres,
            $u->apellidos,
            $u->correo,
            $u->telefono,
            $u->facultad,
            $u->tipo_registro,
            $u->estudiante?->registro,
            $roles,
            (bool) $u->activo,
        );
    }
}
