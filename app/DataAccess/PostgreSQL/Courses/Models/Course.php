<?php

namespace App\DataAccess\PostgreSQL\Courses\Models;

use App\DataAccess\PostgreSQL\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tabla "curso". */
final class Course extends Model
{
    protected $table = 'curso';

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
        'id_categoria',
        'fecha_inicio',
        'fecha_finalizacion',
        'estado',
        'motivo_cancelacion',
        'fecha_cancelacion',
    ];

    protected function casts(): array
    {
        return [
            'cupos' => 'integer',
            'es_gratuito' => 'boolean',
            'fecha_inicio' => 'date',
            'fecha_finalizacion' => 'date',
            'fecha_cancelacion' => 'datetime',
        ];
    }

    /** Usuario del tutor propietario (curso.id_tutor → tutor.id_usuario → usuario). */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_tutor', 'id_usuario');
    }
}
