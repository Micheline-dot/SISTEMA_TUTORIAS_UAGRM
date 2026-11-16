<?php

namespace App\DataAccess\PostgreSQL\Inscripciones\Models;

use App\DataAccess\PostgreSQL\Courses\Models\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla "curso_inscripcion" (PK compuesta id_inscripcion + id_curso).
 * estado: activa, asistio (tutor registró asistencia), confirmada (estudiante
 * confirmó participación), cancelada, eliminada (retirado por el tutor).
 */
final class CursoInscripcion extends Model
{
    public const PENDIENTE = 'activa';
    public const ASISTENCIA_REGISTRADA = 'asistio';
    public const PARTICIPACION_CONFIRMADA = 'confirmada';
    public const CANCELADA = 'cancelada';
    public const ELIMINADA = 'eliminada';

    /** Estados que cuentan como participación verificada por el tutor. */
    public const VERIFICADOS = [self::ASISTENCIA_REGISTRADA, self::PARTICIPACION_CONFIRMADA];

    protected $table = 'curso_inscripcion';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['id_curso', 'id_inscripcion', 'estado'];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_curso', 'id_curso');
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion', 'id_inscripcion');
    }
}
