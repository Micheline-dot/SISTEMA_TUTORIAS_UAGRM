<?php

namespace App\DataAccess\PostgreSQL\Courses\Repositories;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CourseDraftData;
use App\Business\Cursos\DTO\CourseDraftPatch;
use App\Business\Cursos\DTO\CourseModality;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\DTO\CourseSummary;
use App\DataAccess\PostgreSQL\Courses\Models\Course;
use App\DataAccess\PostgreSQL\Users\Repositories\PostgreSqlIntegerId;

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
            'estado' => CourseStatus::BORRADOR->value,
        ]);

        return $this->toDetails($course);
    }

    public function listByOwner(string $ownerTutorId): array
    {
        $courses = Course::query()
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
        $course = Course::query()->find(
            PostgreSqlIntegerId::toInteger($courseId)
        );

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
        );
    }
}