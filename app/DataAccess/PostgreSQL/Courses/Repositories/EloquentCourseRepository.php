<?php

namespace App\DataAccess\PostgreSQL\Courses\Repositories;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CourseCatalogFilters;
use App\Business\Cursos\DTO\PublishedCourseSummary;
use App\Business\Cursos\DTO\CourseDraftData;
use App\Business\Cursos\DTO\CourseDraftPatch;
use App\Business\Cursos\DTO\CourseModality;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\DTO\CourseSummary;
use App\DataAccess\PostgreSQL\Courses\Models\Course;
use App\DataAccess\PostgreSQL\Users\Repositories\PostgreSqlIntegerId;
use Illuminate\Support\Facades\DB;

final class EloquentCourseRepository implements CourseRepository
{
    public function createDraft(
        string $ownerTutorId,
        CourseDraftData $data,
    ): CourseDetails {
        $course = Course::query()->create([
            'id_tutor' => PostgreSqlIntegerId::toInteger($ownerTutorId),
            'titulo' => $data->title,
            'descripcion' => $data->description,
            'modalidad' => $data->modality?->value,
            'cupos' => $data->totalSeats,
            'es_gratuito' => $data->isFree,
            'id_categoria' => $data->categoryId === null ? null : (int) $data->categoryId,
            'fecha_inicio' => $data->startDate,
            'fecha_finalizacion' => $data->endDate,
            'estado' => CourseStatus::BORRADOR->value,
        ]);

        return $this->toDetails($course->fresh());
    }

    public function listByOwner(string $ownerTutorId): array
    {
        $courses = Course::query()
            ->with('categoria')
            ->withCount(['inscripcionesActivas as inscritos_activos'])
            ->where(
                'id_tutor',
                PostgreSqlIntegerId::toInteger($ownerTutorId)
            )
            ->orderByDesc('id_curso')
            ->get();

        return $courses
            ->map(fn (Course $course): CourseSummary => $this->toSummary($course))
            ->all();
    }

    public function findById(string $courseId): ?CourseDetails
    {
        $course = Course::query()
            ->with('categoria')
            ->withCount(['inscripcionesActivas as inscritos_activos'])
            ->find(PostgreSqlIntegerId::toInteger($courseId));

        return $course === null ? null : $this->toDetails($course);
    }

    public function updateDraftOwnedBy(
        string $courseId,
        string $ownerTutorId,
        CourseDraftPatch $patch,
    ): ?CourseDetails {
        $id = PostgreSqlIntegerId::toInteger($courseId);
        $ownerId = PostgreSqlIntegerId::toInteger($ownerTutorId);

        $changes = [];

        if ($patch->title !== null) {
            $changes['titulo'] = $patch->title->value;
        }

        if ($patch->description !== null) {
            $changes['descripcion'] = $patch->description->value;
        }

        if ($patch->modality !== null) {
            $value = $patch->modality->value;
            $changes['modalidad'] = $value instanceof CourseModality
                ? $value->value
                : $value;
        }

        if ($patch->totalSeats !== null) {
            $changes['cupos'] = $patch->totalSeats->value;
        }

        if ($patch->categoryId !== null) {
            $changes['id_categoria'] = $patch->categoryId->value === null ? null : (int) $patch->categoryId->value;
        }

        if ($patch->startDate !== null) {
            $changes['fecha_inicio'] = $patch->startDate->value;
        }

        if ($patch->endDate !== null) {
            $changes['fecha_finalizacion'] = $patch->endDate->value;
        }

        if ($patch->isFree !== null) {
            $changes['es_gratuito'] = $patch->isFree->value;
        }

        $query = Course::query()
            ->where('id_curso', $id)
            ->where('id_tutor', $ownerId)
            ->where('estado', CourseStatus::BORRADOR->value);

        if ($changes !== []) {
            $updated = $query->update($changes);

            if ($updated === 0) {
                return null;
            }
        }

        $course = Course::query()->find($id);

        if (
            $course === null
            || (int) $course->id_tutor !== $ownerId
            || $course->estado !== CourseStatus::BORRADOR->value
        ) {
            return null;
        }

        return $this->toDetails($course);
    }

    public function publishDraftOwnedBy(
        string $courseId,
        string $ownerTutorId,
    ): ?CourseDetails {
        $id = PostgreSqlIntegerId::toInteger($courseId);
        $ownerId = PostgreSqlIntegerId::toInteger($ownerTutorId);

        $updated = Course::query()
            ->where('id_curso', $id)
            ->where('id_tutor', $ownerId)
            ->where('estado', CourseStatus::BORRADOR->value)
            ->update([
                'estado' => CourseStatus::PUBLICADO->value,
            ]);

        if ($updated !== 1) {
            return null;
        }

        $course = Course::query()->find($id);

        return $course === null
            ? null
            : $this->toDetails($course);
    }

    public function searchPublished(
        CourseCatalogFilters $filters,
    ): array {
        // Inscritos activos por curso y valoración promedio de cada tutor (CU16).
        $inscritos = DB::table('curso_inscripcion as ci')
            ->join('inscripcion as i', 'i.id_inscripcion', '=', 'ci.id_inscripcion')
            ->where('i.estado', true)
            ->groupBy('ci.id_curso')
            ->select('ci.id_curso', DB::raw('COUNT(*) AS activos'));

        $valoraciones = DB::table('calificacion_resena as cr')
            ->join('curso as cv', 'cv.id_curso', '=', 'cr.id_curso')
            ->groupBy('cv.id_tutor')
            ->select('cv.id_tutor', DB::raw('AVG(cr.puntuacion) AS promedio'), DB::raw('COUNT(*) AS total'));

        $query = DB::table('curso as c')
            ->join('usuario as u', 'u.id_usuario', '=', 'c.id_tutor')
            ->leftJoin('categoria as cat', 'cat.id_categoria', '=', 'c.id_categoria')
            ->leftJoinSub($inscritos, 'ins', 'ins.id_curso', '=', 'c.id_curso')
            ->leftJoinSub($valoraciones, 'val', 'val.id_tutor', '=', 'c.id_tutor')
            ->where('c.estado', CourseStatus::PUBLICADO->value)
            ->select([
                'c.id_curso', 'c.id_tutor', 'c.titulo', 'c.descripcion', 'c.modalidad', 'c.cupos',
                'c.es_gratuito', 'c.fecha_inicio', 'c.fecha_finalizacion', 'cat.nombre as categoria',
                DB::raw("TRIM(u.nombres || ' ' || u.apellidos) AS tutor"),
                DB::raw('COALESCE(ins.activos, 0) AS activos'),
                'val.promedio', DB::raw('COALESCE(val.total, 0) AS valoraciones'),
                'c.portada', 'u.foto_perfil',
            ]);

        // CU11: título, descripción, nombre del tutor o categoría.
        if ($filters->search !== null && trim($filters->search) !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($filters->search)).'%';

            $query->where(function ($q) use ($like): void {
                $q->where('c.titulo', 'ILIKE', $like)
                    ->orWhere('c.descripcion', 'ILIKE', $like)
                    ->orWhereRaw("(u.nombres || ' ' || u.apellidos) ILIKE ?", [$like])
                    ->orWhere('cat.nombre', 'ILIKE', $like);
            });
        }

        // CU12: filtros.
        if ($filters->modality !== null) {
            $query->where('c.modalidad', $filters->modality->value);
        }

        if ($filters->isFree !== null) {
            $query->where('c.es_gratuito', $filters->isFree);
        }

        if ($filters->categoryId !== null && ctype_digit($filters->categoryId)) {
            $query->where('c.id_categoria', (int) $filters->categoryId);
        }

        if ($filters->tutorId !== null && ctype_digit($filters->tutorId)) {
            $query->where('c.id_tutor', (int) $filters->tutorId);
        }

        if ($filters->minRating !== null) {
            $query->where('val.promedio', '>=', $filters->minRating);
        }

        if ($filters->onlyAvailable) {
            $query->whereRaw('(c.cupos IS NULL OR c.cupos > COALESCE(ins.activos, 0))');
        }

        match ($filters->sort) {
            'PROXIMOS' => $query->orderByRaw('c.fecha_inicio IS NULL, c.fecha_inicio')->orderByDesc('c.id_curso'),
            'VALORACION' => $query->orderByRaw('val.promedio DESC NULLS LAST')->orderByDesc('c.id_curso'),
            default => $query->orderByDesc('c.id_curso'),
        };

        return $query->get()
            ->map(static fn (object $r): PublishedCourseSummary => new PublishedCourseSummary(
                id: (string) $r->id_curso,
                tutorId: (string) $r->id_tutor,
                tutorName: $r->tutor !== '' ? $r->tutor : 'Tutor UAGRM',
                title: $r->titulo,
                description: $r->descripcion,
                modality: $r->modalidad === null ? null : CourseModality::from($r->modalidad),
                totalSeats: $r->cupos === null ? null : (int) $r->cupos,
                isFree: $r->es_gratuito === null ? null : (bool) $r->es_gratuito,
                categoryName: $r->categoria,
                startDate: $r->fecha_inicio === null ? null : substr((string) $r->fecha_inicio, 0, 10),
                endDate: $r->fecha_finalizacion === null ? null : substr((string) $r->fecha_finalizacion, 0, 10),
                activeEnrollments: (int) $r->activos,
                tutorRating: $r->promedio === null ? null : round((float) $r->promedio, 1),
                tutorRatingCount: (int) $r->valoraciones,
                hasCover: $r->portada !== null,
                tutorHasPhoto: $r->foto_perfil !== null,
            ))
            ->all();
    }
    private function toDetails(Course $course): CourseDetails
    {
        return new CourseDetails(
            id: PostgreSqlIntegerId::toString($course->id_curso),
            ownerTutorId: PostgreSqlIntegerId::toString($course->id_tutor),
            status: CourseStatus::from($course->estado),
            title: $course->titulo,
            description: $course->descripcion,
            modality: $course->modalidad === null
                ? null
                : CourseModality::from($course->modalidad),
            totalSeats: $course->cupos,
            isFree: $course->es_gratuito,
            categoryId: $course->id_categoria === null ? null : (string) $course->id_categoria,
            categoryName: $course->categoria?->nombre,
            startDate: $course->fecha_inicio?->toDateString(),
            endDate: $course->fecha_finalizacion?->toDateString(),
            cancellationReason: $course->motivo_cancelacion,
            activeEnrollments: (int) ($course->inscritos_activos ?? $course->inscripcionesActivas()->count()),
            hasCover: $course->portada !== null,
        );
    }

    private function toSummary(Course $course): CourseSummary
    {
        return new CourseSummary(
            id: PostgreSqlIntegerId::toString($course->id_curso),
            ownerTutorId: PostgreSqlIntegerId::toString($course->id_tutor),
            status: CourseStatus::from($course->estado),
            title: $course->titulo,
            modality: $course->modalidad === null
                ? null
                : CourseModality::from($course->modalidad),
            categoryName: $course->categoria?->nombre,
            startDate: $course->fecha_inicio?->toDateString(),
            endDate: $course->fecha_finalizacion?->toDateString(),
            totalSeats: $course->cupos,
            activeEnrollments: (int) ($course->inscritos_activos ?? 0),
        );
    }
}