<?php

namespace App\DataAccess\PostgreSQL\Courses\Repositories;

use App\Business\Cursos\Contracts\SyllabusRepository;
use App\Business\Cursos\DTO\SyllabusResource;
use App\Business\Cursos\DTO\SyllabusTopic;
use Illuminate\Support\Facades\DB;

/** temario (PK id_curso + id_temario) y recurso (FK compuesta a temario). */
final class EloquentSyllabusRepository implements SyllabusRepository
{
    public function topicsOf(string $courseId): array
    {
        $topics = DB::table('temario')
            ->where('id_curso', (int) $courseId)
            ->orderByRaw('fecha IS NULL, fecha, id_temario')
            ->get();

        $resources = DB::table('recurso')
            ->where('id_curso', (int) $courseId)
            ->orderBy('id_recurso')
            ->get()
            ->groupBy('id_temario');

        return $topics->map(fn (object $t): SyllabusTopic => new SyllabusTopic(
            (string) $t->id_temario,
            $t->nombre,
            $t->fecha === null ? null : substr((string) $t->fecha, 0, 10),
            $t->estado ?? 'PENDIENTE',
            ($resources->get($t->id_temario) ?? collect())
                ->map(fn (object $r): SyllabusResource => new SyllabusResource((string) $r->id_recurso, $r->nombre, $r->enlace))
                ->values()
                ->all(),
        ))->all();
    }

    public function countTopics(string $courseId): int
    {
        return DB::table('temario')->where('id_curso', (int) $courseId)->count();
    }

    public function topicBelongsToCourse(string $courseId, string $topicId): bool
    {
        return DB::table('temario')
            ->where('id_curso', (int) $courseId)
            ->where('id_temario', (int) $topicId)
            ->exists();
    }

    public function addTopic(string $courseId, string $name, ?string $date): string
    {
        return (string) DB::table('temario')->insertGetId([
            'id_curso' => (int) $courseId,
            'nombre' => $name,
            'fecha' => $date,
            'estado' => 'PENDIENTE',
        ], 'id_temario');
    }

    public function updateTopic(string $courseId, string $topicId, string $name, ?string $date, string $status): void
    {
        DB::table('temario')
            ->where('id_curso', (int) $courseId)
            ->where('id_temario', (int) $topicId)
            ->update(['nombre' => $name, 'fecha' => $date, 'estado' => $status]);
    }

    public function deleteTopic(string $courseId, string $topicId): void
    {
        // recurso se elimina en cascada (FK compuesta).
        DB::table('temario')
            ->where('id_curso', (int) $courseId)
            ->where('id_temario', (int) $topicId)
            ->delete();
    }

    public function addResource(string $courseId, string $topicId, string $name, string $url): string
    {
        return (string) DB::table('recurso')->insertGetId([
            'id_curso' => (int) $courseId,
            'id_temario' => (int) $topicId,
            'nombre' => $name,
            'enlace' => $url,
        ], 'id_recurso');
    }

    public function deleteResource(string $courseId, string $resourceId): bool
    {
        return DB::table('recurso')
            ->where('id_curso', (int) $courseId)
            ->where('id_recurso', (int) $resourceId)
            ->delete() > 0;
    }
}
