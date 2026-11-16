<?php

namespace App\Business\Categorias\UseCases;

use App\Business\Categorias\Contracts\CategoryRepository;
use App\Business\Categorias\DTO\Category;
use App\Business\Categorias\Exceptions\CategoryRuleViolation;

/**
 * CU21 Gestionar categorías (administrador). Una categoría usada por algún
 * curso no se puede eliminar (se puede renombrar).
 */
final class ManageCategories
{
    public function __construct(private readonly CategoryRepository $categories)
    {
    }

    /** @return list<Category> */
    public function list(): array
    {
        return $this->categories->all();
    }

    public function create(string $name): string
    {
        return $this->categories->create($this->cleanName($name));
    }

    public function rename(string $id, string $name): void
    {
        $this->findOrFail($id);
        $this->categories->rename($id, $this->cleanName($name));
    }

    public function delete(string $id): void
    {
        $category = $this->findOrFail($id);

        if ($category->courseCount > 0) {
            throw new CategoryRuleViolation(
                "No se puede eliminar \"{$category->name}\": la usan {$category->courseCount} tutoría(s). Puedes renombrarla."
            );
        }

        $this->categories->delete($id);
    }

    private function findOrFail(string $id): Category
    {
        return (ctype_digit($id) ? $this->categories->find($id) : null)
            ?? throw new CategoryRuleViolation('La categoría no existe.');
    }

    private function cleanName(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name));

        if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
            throw new CategoryRuleViolation('El nombre debe tener entre 3 y 100 caracteres.');
        }

        return mb_strtoupper(mb_substr($name, 0, 1)).mb_substr($name, 1);
    }
}
