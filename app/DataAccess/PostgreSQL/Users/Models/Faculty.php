<?php

namespace App\DataAccess\PostgreSQL\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Faculty extends Model
{
    protected $table = 'facultades';

    protected $primaryKey = 'id_facultad';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function academicProfiles(): HasMany
    {
        return $this->hasMany(AcademicProfile::class, 'id_facultad', 'id_facultad');
    }
}
