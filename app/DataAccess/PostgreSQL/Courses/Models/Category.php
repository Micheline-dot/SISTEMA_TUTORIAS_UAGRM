<?php

namespace App\DataAccess\PostgreSQL\Courses\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabla "categoria". */
final class Category extends Model
{
    protected $table = 'categoria';

    protected $primaryKey = 'id_categoria';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['nombre'];
}
