<?php

namespace App\DataAccess\PostgreSQL\Courses\Repositories;

use App\Business\Cursos\Contracts\CourseCoverRepository;
use Illuminate\Support\Facades\DB;

final class DbCourseCoverRepository implements CourseCoverRepository
{
    public function find(string $courseId): ?array
    {
        $row = DB::table('curso')->where('id_curso', (int) $courseId)->first(['portada', 'id_tutor', 'estado']);

        return $row === null ? null : [
            'path' => $row->portada,
            'ownerId' => (string) $row->id_tutor,
            'status' => $row->estado,
        ];
    }

    public function updatePath(string $courseId, ?string $path): void
    {
        DB::table('curso')->where('id_curso', (int) $courseId)->update(['portada' => $path]);
    }
}
