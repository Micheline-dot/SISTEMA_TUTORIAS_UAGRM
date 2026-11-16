<?php

namespace App\Business\Usuarios\Contracts;

use App\Business\Usuarios\DTO\UserSummary;

/** CU20 Gestionar usuarios (administrador). */
interface UserAdminRepository
{
    /**
     * @param string $role   '' | ESTUDIANTE | TUTOR | ADMINISTRADOR
     * @param string $status '' | ACTIVO | INACTIVO
     * @return array{items: list<UserSummary>, total: int}
     */
    public function search(string $text, string $role, string $status, int $page, int $perPage): array;

    public function find(string $userId): ?UserSummary;

    public function setActive(string $userId, bool $active): void;

    public function countActiveAdministrators(): int;

    /** @return list<string> ids de los cursos PUBLICADOS del tutor */
    public function publishedCourseIdsOfTutor(string $userId): array;
}
