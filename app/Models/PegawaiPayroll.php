<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PegawaiPayroll extends Model
{
    protected $table    = 'hr_pegawai_payroll';
    protected $fillable = ['nik', 'golongan', 'umk_tahun', 'catatan'];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik', 'nik');
    }
}
