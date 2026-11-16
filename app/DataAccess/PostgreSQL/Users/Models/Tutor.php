<?php

namespace App\DataAccess\PostgreSQL\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Tabla "tutor". */
final class Tutor extends Model
{
    protected $table = 'tutor';

    protected $primaryKey = 'id_tutor';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['id_tutor', 'tipo_academico'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_tutor', 'id_usuario');
    }

    public function perfilAcademico(): HasOne
    {
        return $this->hasOne(AcademicProfile::class, 'id_tutor', 'id_tutor');
    }
}
