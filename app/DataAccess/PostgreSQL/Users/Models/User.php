<?php

namespace App\DataAccess\PostgreSQL\Users\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Tabla "usuario". Los roles se representan con las especializaciones
 * estudiante, tutor y administrador (una fila por rol).
 */
final class User extends Authenticatable
{
    public const ROL_ESTUDIANTE = 'ESTUDIANTE';
    public const ROL_TUTOR = 'TUTOR';
    public const ROL_ADMINISTRADOR = 'ADMINISTRADOR';

    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nombres',
        'apellidos',
        'correo',
        'contrasena',
        'telefono',
        'facultad',
        'tipo_registro',
        'activo',
        'foto_perfil',
        'registro_auxiliar',
        'codigo_docente',
    ];

    protected $hidden = [
        'contrasena',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function estudiante(): HasOne
    {
        return $this->hasOne(Student::class, 'id_estudiante', 'id_usuario');
    }

    public function tutor(): HasOne
    {
        return $this->hasOne(Tutor::class, 'id_tutor', 'id_usuario');
    }

    public function administrador(): HasOne
    {
        return $this->hasOne(Administrator::class, 'id_administrador', 'id_usuario');
    }

    /** @return list<string> */
    public function nombresRoles(): array
    {
        $roles = [];

        if ($this->estudiante()->exists()) {
            $roles[] = self::ROL_ESTUDIANTE;
        }

        if ($this->tutor()->exists()) {
            $roles[] = self::ROL_TUTOR;
        }

        if ($this->administrador()->exists()) {
            $roles[] = self::ROL_ADMINISTRADOR;
        }

        return $roles;
    }

    public function tieneRol(string $rol): bool
    {
        return match ($rol) {
            self::ROL_ESTUDIANTE => $this->estudiante()->exists(),
            self::ROL_TUTOR => $this->tutor()->exists(),
            self::ROL_ADMINISTRADOR => $this->administrador()->exists(),
            default => false,
        };
    }

    /** Nombre para mostrar en pantallas: "nombres apellidos". */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(
            fn (): string => trim(($this->nombres ?? '').' '.($this->apellidos ?? ''))
        );
    }
}
