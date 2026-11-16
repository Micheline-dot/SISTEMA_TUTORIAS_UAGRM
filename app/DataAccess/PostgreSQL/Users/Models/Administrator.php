<?php

namespace App\DataAccess\PostgreSQL\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tabla "administrador". */
final class Administrator extends Model
{
    protected $table = 'administrador';

    protected $primaryKey = 'id_administrador';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['id_administrador', 'cargo'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_administrador', 'id_usuario');
    }
}
