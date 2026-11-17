<?php

namespace App\DataAccess\PostgreSQL\Courses\Repositories;

use App\Business\Cursos\Contracts\TutorRatingRepository;
use Illuminate\Support\Facades\DB;

final class EloquentTutorRatingRepository implements TutorRatingRepository
{
    public function summaryForTutor(string $tutorId): array
    {
        $row = DB::table('calificacion_resena as cr')
            ->join('curso as c', 'c.id_curso', '=', 'cr.id_curso')
            ->where('c.id_tutor', (int) $tutorId)
            ->selectRaw('AVG(cr.puntuacion) AS promedio, COUNT(*) AS total')
            ->first();

        return [
            'average' => $row?->promedio === null ? null : round((float) $row->promedio, 1),
            'count' => (int) ($row->total ?? 0),
        ];
    }
}
