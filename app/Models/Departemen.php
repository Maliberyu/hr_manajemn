<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departemen extends Model
{
    public $timestamps = false;

    protected $table      = 'departemen';
    protected $primaryKey = 'dep_id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = ['dep_id', 'nama'];

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'departemen', 'dep_id');
    }
}
