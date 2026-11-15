<?php

namespace App\DataAccess\PostgreSQL\Courses\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\DataAccess\PostgreSQL\Users\Models\User;

final class Course extends Model
{
    protected $table = 'cursos';

    protected $primaryKey = 'id_curso';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_tutor',
        'titulo',
        'descripcion',
        'modalidad',
        'cupos',
        'es_gratuito',
        'fecha_inicio',
        'fecha_finalizacion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'cupos' => 'integer',
            'es_gratuito' => 'boolean',
            'fecha_inicio' => 'date',
            'fecha_finalizacion' => 'date',
        ];
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_tutor',
            'id_usuario'
        );
    }
}