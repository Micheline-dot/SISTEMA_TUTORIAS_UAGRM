<?php

namespace App\DataAccess\PostgreSQL\Courses\Models;

use App\DataAccess\PostgreSQL\Inscripciones\Models\CursoInscripcion;
use App\DataAccess\PostgreSQL\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_categoria', 'id_categoria');
    }

    /** Inscripciones vigentes (inscripcion.estado = true) a este curso. */
    public function inscripcionesActivas(): HasMany
    {
        return $this->hasMany(CursoInscripcion::class, 'id_curso', 'id_curso')
            ->whereHas('inscripcion', fn ($q) => $q->where('estado', true));
    }
}
