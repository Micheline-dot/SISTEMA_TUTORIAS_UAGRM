<?php

namespace App\DataAccess\PostgreSQL\Habilitaciones\Models;

use App\DataAccess\PostgreSQL\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SolicitudHabilitacionTutor extends Model
{
    protected $table = 'solicitudes_habilitacion_tutor';

    protected $primaryKey = 'id_solicitud';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_usuario',
        'tipo_personal',
        'codigo_doc',
        'codigo_auxiliar',
        'documento_respaldo',
        'carta_solicitud',
        'registro_academico',
        'historial_academico',
        'curriculum',
        'estado',
        'id_administrador_revisor',
        'observacion',
        'fecha_revision',
    ];

    protected function casts(): array
    {
        return [
            'fecha_revision' => 'datetime',
        ];
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_usuario',
            'id_usuario'
        );
    }

    public function administradorRevisor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_administrador_revisor',
            'id_usuario'
        );
    }
}