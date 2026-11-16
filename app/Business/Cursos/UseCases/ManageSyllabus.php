<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\SyllabusRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\DTO\SyllabusTopic;
use App\Business\Cursos\Policies\CourseDraftRules;
use App\Business\Cursos\Policies\CourseOwnershipPolicy;

/**
 * CU08 Gestionar temarios: el tutor dueño organiza los temas (nombre, fecha,
 * estado) y sus recursos (enlaces). Editable en BORRADOR y PUBLICADO; un curso
 * cancelado o finalizado queda de solo lectura.
 */
final class ManageSyllabus
{
    public const MAX_TOPICS = 50;
    public const MAX_RESOURCES_PER_TOPIC = 10;
    public const STATUSES = ['PENDIENTE', 'COMPLETADO'];

    public function __construct(
        private readonly CourseRepository $courses,
        private readonly SyllabusRepository $syllabus,
        private readonly TutorEligibility $tutors,
        private readonly CourseOwnershipPolicy $ownership,
    ) {
    }

    /** @return list<SyllabusTopic> */
    public function topics(string $tutorId, string $courseId): array
    {
        $this->ownedCourse($tutorId, $courseId);

        return $this->syllabus->topicsOf($courseId);
    }

    public function addTopic(string $tutorId, string $courseId, string $name, ?string $date): string
    {
        $course = $this->editableCourse($tutorId, $courseId);

        if ($this->syllabus->countTopics($courseId) >= self::MAX_TOPICS) {
            throw new CourseRuleViolation('El temario admite como máximo '.self::MAX_TOPICS.' temas.');
        }

        return $this->syllabus->addTopic($courseId, $this->name($name), $this->date($course, $date));
    }

    public function updateTopic(string $tutorId, string $courseId, string $topicId, string $name, ?string $date, string $status): void
    {
        $course = $this->editableCourse($tutorId, $courseId);
        $this->assertTopic($courseId, $topicId);

        if (! in_array($status, self::STATUSES, true)) {
            throw new CourseRuleViolation('Estado de tema no válido.');
        }

        $this->syllabus->updateTopic($courseId, $topicId, $this->name($name), $this->date($course, $date), $status);
    }

    public function deleteTopic(string $tutorId, string $courseId, string $topicId): void
    {
        $course = $this->editableCourse($tutorId, $courseId);
        $this->assertTopic($courseId, $topicId);

        // Un curso publicado debe conservar al menos un tema.
        if ($course->status === CourseStatus::PUBLICADO && $this->syllabus->countTopics($courseId) <= 1) {
            throw new CourseRuleViolation('Una tutoría publicada debe conservar al menos un tema.');
        }

        $this->syllabus->deleteTopic($courseId, $topicId);
    }

    public function addResource(string $tutorId, string $courseId, string $topicId, string $name, string $url): string
    {
        $this->editableCourse($tutorId, $courseId);
        $this->assertTopic($courseId, $topicId);

        foreach ($this->syllabus->topicsOf($courseId) as $topic) {
            if ($topic->id === $topicId && count($topic->resources) >= self::MAX_RESOURCES_PER_TOPIC) {
                throw new CourseRuleViolation('Cada tema admite como máximo '.self::MAX_RESOURCES_PER_TOPIC.' recursos.');
            }
        }

        $url = trim($url);
        if (mb_strlen($url) > 255 || filter_var($url, FILTER_VALIDATE_URL) === false || ! preg_match('#^https?://#i', $url)) {
            throw new CourseRuleViolation('El enlace debe ser una dirección completa que empiece con http:// o https://.');
        }

        return $this->syllabus->addResource($courseId, $topicId, $this->name($name), $url);
    }

    public function deleteResource(string $tutorId, string $courseId, string $resourceId): void
    {
        $this->editableCourse($tutorId, $courseId);

        if (! ctype_digit($resourceId) || ! $this->syllabus->deleteResource($courseId, $resourceId)) {
            throw new CourseRuleViolation('El recurso no existe.');
        }
    }

    private function ownedCourse(string $tutorId, string $courseId): CourseDetails
    {
        if (! $this->tutors->isEnabledTutor($tutorId)) {
            throw new TutorNotEnabled('Only enabled tutors can manage syllabi.');
        }

        $course = ctype_digit($courseId) ? $this->courses->findById($courseId) : null;

        if ($course === null) {
            throw new CourseNotFound('Course was not found.');
        }

        $this->ownership->assertOwner($tutorId, $course);

        return $course;
    }

    private function editableCourse(string $tutorId, string $courseId): CourseDetails
    {
        $course = $this->ownedCourse($tutorId, $courseId);

        if (! in_array($course->status, [CourseStatus::BORRADOR, CourseStatus::PUBLICADO], true)) {
            throw new CourseRuleViolation('El temario de una tutoría cancelada o finalizada no se puede modificar.');
        }

        return $course;
    }

    private function assertTopic(string $courseId, string $topicId): void
    {
        if (! ctype_digit($topicId) || ! $this->syllabus->topicBelongsToCourse($courseId, $topicId)) {
            throw new CourseRuleViolation('El tema no existe.');
        }
    }

    private function name(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name));

        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            throw new CourseRuleViolation('El nombre debe tener entre 2 y 150 caracteres.');
        }

        return $name;
    }

    /** Fecha opcional; si el curso ya tiene fechas, debe estar dentro del periodo. */
    private function date(CourseDetails $course, ?string $date): ?string
    {
        $date = $date === null || trim($date) === '' ? null : trim($date);

        if ($date === null) {
            return null;
        }

        if (! CourseDraftRules::isValidDate($date)) {
            throw new CourseRuleViolation('La fecha del tema no es válida.');
        }

        if (($course->startDate !== null && $date < $course->startDate)
            || ($course->endDate !== null && $date > $course->endDate)) {
            throw new CourseRuleViolation('La fecha del tema debe estar dentro del periodo de la tutoría.');
        }

        return $date;
    }
}
