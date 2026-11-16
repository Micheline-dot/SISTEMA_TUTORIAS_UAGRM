<?php

namespace App\Business\Categorias\Contracts;

use App\Business\Categorias\DTO\Category;

interface CategoryRepository
{
    /** @return list<Category> ordenadas por nombre, con cantidad de cursos */
    public function all(): array;

    public function find(string $id): ?Category;

    public function exists(string $id): bool;

    /** @throws \App\Business\Categorias\Exceptions\CategoryRuleViolation si el nombre ya existe */
    public function create(string $name): string;

    /** @throws \App\Business\Categorias\Exceptions\CategoryRuleViolation si el nombre ya existe */
    public function rename(string $id, string $name): void;

    public function delete(string $id): void;
}
