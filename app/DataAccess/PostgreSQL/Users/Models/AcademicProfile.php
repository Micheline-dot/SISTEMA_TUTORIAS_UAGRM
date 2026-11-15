<?php

namespace App\DataAccess\PostgreSQL\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AcademicProfile extends Model
{
    protected $table = 'perfil_academico';

    protected $primaryKey = 'id_perfil_academico';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['id_usuario', 'id_facultad'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'id_facultad', 'id_facultad');
    }
}
