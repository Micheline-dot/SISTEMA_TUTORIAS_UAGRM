<?php

namespace App\DataAccess\PostgreSQL\Categorias\Repositories;

use App\Business\Categorias\Contracts\CategoryRepository;
use App\Business\Categorias\DTO\Category;
use App\Business\Categorias\Exceptions\CategoryRuleViolation;
use Illuminate\Support\Facades\DB;

final class EloquentCategoryRepository implements CategoryRepository
{
    public function all(): array
    {
        return $this->baseQuery()
            ->orderBy('c.nombre')
            ->get()
            ->map(fn (object $r): Category => $this->toCategory($r))
            ->all();
    }

    public function find(string $id): ?Category
    {
        $row = $this->baseQuery()->where('c.id_categoria', (int) $id)->first();

        return $row === null ? null : $this->toCategory($row);
    }

    public function exists(string $id): bool
    {
        return ctype_digit($id) && DB::table('categoria')->where('id_categoria', (int) $id)->exists();
    }

    public function create(string $name): string
    {
        $this->assertUniqueName($name, null);

        return (string) DB::table('categoria')->insertGetId(['nombre' => $name], 'id_categoria');
    }

    public function rename(string $id, string $name): void
    {
        $this->assertUniqueName($name, $id);

        DB::table('categoria')->where('id_categoria', (int) $id)->update(['nombre' => $name]);
    }

    public function delete(string $id): void
    {
        DB::table('categoria')->where('id_categoria', (int) $id)->delete();
    }

    /** Nombre único sin distinguir mayúsculas ni tildes de más espacios. */
    private function assertUniqueName(string $name, ?string $exceptId): void
    {
        $exists = DB::table('categoria')
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($name)])
            ->when($exceptId !== null, fn ($q) => $q->where('id_categoria', '<>', (int) $exceptId))
            ->exists();

        if ($exists) {
            throw new CategoryRuleViolation("Ya existe una categoría llamada \"{$name}\".");
        }
    }

    private function baseQuery()
    {
        return DB::table('categoria as c')
            ->leftJoin('curso as cu', 'cu.id_categoria', '=', 'c.id_categoria')
            ->groupBy('c.id_categoria', 'c.nombre')
            ->select('c.id_categoria', 'c.nombre', DB::raw('COUNT(cu.id_curso) AS cursos'));
    }

    private function toCategory(object $r): Category
    {
        return new Category((string) $r->id_categoria, $r->nombre, (int) $r->cursos);
    }
}
