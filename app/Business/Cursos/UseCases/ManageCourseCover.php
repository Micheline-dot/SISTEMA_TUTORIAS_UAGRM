<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Cursos\Contracts\CourseCoverRepository;
use App\Business\Cursos\Contracts\CourseCoverStorage;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Cursos\DTO\CourseStatus;

/**
 * (+) Portada de la tutoría: el tutor dueño la sube o quita mientras la
 * tutoría es borrador o está publicada. La ve cualquier usuario autenticado
 * si la tutoría no es borrador; el borrador solo lo ve su tutor.
 */
final class ManageCourseCover
{
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public const MAX_KILOBYTES = 3072;

    public function __construct(
        private readonly CourseCoverRepository $courses,
        private readonly CourseCoverStorage $storage,
        private readonly TutorEligibility $tutors,
    ) {
    }

    public function update(string $tutorId, string $courseId, string $temporaryPath, string $extension): void
    {
        $extension = strtolower($extension);
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new CourseRuleViolation('La portada debe ser una imagen JPG, PNG o WEBP.');
        }

        $course = $this->editable($tutorId, $courseId);
        $newPath = $this->storage->store($courseId, $temporaryPath, $extension);

        try {
            $this->courses->updatePath($courseId, $newPath);
        } catch (\Throwable $e) {
            $this->storage->delete($newPath);
            throw $e;
        }

        if ($course['path'] !== null) {
            $this->storage->delete($course['path']);
        }
    }

    public function remove(string $tutorId, string $courseId): void
    {
        $course = $this->editable($tutorId, $courseId);
        if ($course['path'] === null) {
            return;
        }

        $this->courses->updatePath($courseId, null);
        $this->storage->delete($course['path']);
    }

    /** Ruta de la portada que puede ver $viewerId, o null. */
    public function pathFor(string $viewerId, string $courseId): ?string
    {
        $course = ctype_digit($courseId) ? $this->courses->find($courseId) : null;
        if ($course === null || $course['path'] === null) {
            return null;
        }

        if ($course['status'] === CourseStatus::BORRADOR->value && $course['ownerId'] !== $viewerId) {
            return null;
        }

        return $course['path'];
    }

    /** @return array{path: ?string, ownerId: string, status: string} */
    private function editable(string $tutorId, string $courseId): array
    {
        if (! $this->tutors->isEnabledTutor($tutorId)) {
            throw new TutorNotEnabled('Only enabled tutors can manage courses.');
        }

        $course = ctype_digit($courseId) ? $this->courses->find($courseId) : null;
        if ($course === null) {
            throw new CourseNotFound('Course was not found.');
        }
        if ($course['ownerId'] !== $tutorId) {
            throw new CourseNotOwned('Course does not belong to the tutor.');
        }
        if (! in_array($course['status'], [CourseStatus::BORRADOR->value, CourseStatus::PUBLICADO->value], true)) {
            throw new CourseRuleViolation('Solo puedes cambiar la portada de un borrador o de una tutoría publicada.');
        }

        return $course;
    }
}
