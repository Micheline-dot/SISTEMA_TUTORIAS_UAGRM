<?php

namespace App\Business\Cursos\Contracts;

interface CourseCoverRepository
{
    /** @return array{path: ?string, ownerId: string, status: string}|null */
    public function find(string $courseId): ?array;

    public function updatePath(string $courseId, ?string $path): void;
}
