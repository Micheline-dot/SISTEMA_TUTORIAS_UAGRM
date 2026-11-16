<?php

namespace App\DataAccess\PostgreSQL\Habilitaciones\Models;

use App\DataAccess\PostgreSQL\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla "solicitud_habilitacion".
 * estado: pendiente, aprobada, rechazada.
 */
final class SolicitudHabilitacion extends Model
{
    public const PENDIENTE = 'pendiente';
    public const APROBADA = 'aprobada';
    public const RECHAZADA = 'rechazada';

    protected $table = 'solicitud_habilitacion';

    protected $primaryKey = 'id_solicitud';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'estado',
        'fecha',
        'comentario_rechazo',
        'id_administrador',
        'id_estudiante',
        'tipo_academico',
        'codigo',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_estudiante', 'id_usuario');
    }

    public function administrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_administrador', 'id_usuario');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoSolicitud::class, 'id_solicitud', 'id_solicitud');
    }
}
