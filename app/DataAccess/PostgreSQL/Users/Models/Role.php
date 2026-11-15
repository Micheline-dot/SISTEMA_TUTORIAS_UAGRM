<?php

namespace App\DataAccess\PostgreSQL\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Role extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'id_rol';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'usuario_rol',
            'id_rol',
            'id_usuario',
            'id_rol',
            'id_usuario',
        );
    }
}
