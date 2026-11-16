<?php

namespace App\DataAccess\PostgreSQL\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tabla "perfil_academico" (1 a 1 con tutor). */
final class AcademicProfile extends Model
{
    protected $table = 'perfil_academico';

    protected $primaryKey = 'id_perfil_academico';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['id_tutor', 'biografia', 'enlaces', 'conocimiento'];

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'id_tutor', 'id_tutor');
    }
}
