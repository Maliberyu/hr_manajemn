<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pendidikan extends Model
{
    public $timestamps = false;

    protected $table      = 'pendidikan';
    protected $primaryKey = 'tingkat';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = ['tingkat', 'indek'];

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'pendidikan', 'tingkat');
    }
}
