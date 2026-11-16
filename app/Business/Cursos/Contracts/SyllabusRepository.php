<?php

namespace App\Business\Cursos\Contracts;

use App\Business\Cursos\DTO\SyllabusTopic;

/** CU08: tablas temario y recurso. */
interface SyllabusRepository
{
    /** @return list<SyllabusTopic> ordenados por fecha (sin fecha al final) y creación */
    public function topicsOf(string $courseId): array;

    public function countTopics(string $courseId): int;

    public function topicBelongsToCourse(string $courseId, string $topicId): bool;

    public function addTopic(string $courseId, string $name, ?string $date): string;

    public function updateTopic(string $courseId, string $topicId, string $name, ?string $date, string $status): void;

    public function deleteTopic(string $courseId, string $topicId): void;

    public function addResource(string $courseId, string $topicId, string $name, string $url): string;

    /** Devuelve false si el recurso no pertenece a ese curso. */
    public function deleteResource(string $courseId, string $resourceId): bool;
}
