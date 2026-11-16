<?php

namespace App\DataAccess\PostgreSQL\Inscripciones\Models;

use App\DataAccess\PostgreSQL\Courses\Models\Course;
use App\DataAccess\PostgreSQL\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Inscripcion extends Model
{
    protected $table = 'inscripciones';

    protected $primaryKey = 'id_inscripcion';

    public $timestamps = false;

    protected $fillable = [
        'id_curso',
        'id_estudiante',
        'estado',
        'asistencia_verificada',
        'fecha_inscripcion',
        'fecha_cancelacion',
    ];

    protected function casts(): array
    {
        return [
            'asistencia_verificada' => 'boolean',
            'fecha_inscripcion' => 'datetime',
            'fecha_cancelacion' => 'datetime',
        ];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_curso', 'id_curso');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_estudiante', 'id_usuario');
    }
}