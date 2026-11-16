<?php

namespace App\DataAccess\PostgreSQL\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tabla "estudiante". */
final class Student extends Model
{
    protected $table = 'estudiante';

    protected $primaryKey = 'id_estudiante';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['id_estudiante', 'registro'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_estudiante', 'id_usuario');
    }
}
