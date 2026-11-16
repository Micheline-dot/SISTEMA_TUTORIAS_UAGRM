<?php

namespace App\DataAccess\PostgreSQL\Inscripciones\Models;

use App\DataAccess\PostgreSQL\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla "inscripcion". estado: true = inscrito, false = cancelada.
 * El curso se asocia mediante la clase de asociación curso_inscripcion.
 */
final class Inscripcion extends Model
{
    protected $table = 'inscripcion';

    protected $primaryKey = 'id_inscripcion';

    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'estado',
        'motivo_cancelacion',
        'id_usuario',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'estado' => 'boolean',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function cursosInscripcion(): HasMany
    {
        return $this->hasMany(CursoInscripcion::class, 'id_inscripcion', 'id_inscripcion');
    }
}
