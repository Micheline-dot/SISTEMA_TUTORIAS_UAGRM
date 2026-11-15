<?php

namespace App\DataAccess\PostgreSQL\Users\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

final class User extends Authenticatable
{
    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nombre_completo',
        'correo',
        'contrasena_hash',
        'activo',
        'telefono',
    ];

    protected $hidden = [
        'contrasena_hash',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena_hash';
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'usuario_rol',
            'id_usuario',
            'id_rol',
            'id_usuario',
            'id_rol',
        );
    }

    public function perfilAcademico(): HasOne
    {
        return $this->hasOne(AcademicProfile::class, 'id_usuario', 'id_usuario');
    }
}
