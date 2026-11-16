<?php

namespace App\DataAccess\PostgreSQL\Habilitaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla "documento_solicitud".
 * nombre = tipo de documento; ruta = ubicación protegida del PDF.
 */
final class DocumentoSolicitud extends Model
{
    protected $table = 'documento_solicitud';

    protected $primaryKey = 'id_documento';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['id_solicitud', 'nombre', 'ruta'];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudHabilitacion::class, 'id_solicitud', 'id_solicitud');
    }
}
