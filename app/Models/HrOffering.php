<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrOffering extends Model
{
    protected $table    = 'hr_offering';
    protected $fillable = ['pelamar_id', 'gaji_ditawarkan', 'status', 'catatan', 'tanggal_offering', 'updated_by'];
    protected $casts    = [
        'gaji_ditawarkan' => 'float',
        'tanggal_offering'=> 'date',
    ];

    const STATUS = [
        'draft'     => 'Draft',
        'dikirim'   => 'Dikirim',
        'diterima'  => 'Diterima',
        'negosiasi' => 'Negosiasi',
        'ditolak'   => 'Ditolak Kandidat',
    ];

    public function pelamar(): BelongsTo   { return $this->belongsTo(HrPelamar::class, 'pelamar_id'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }

    public function getBadgeStatusAttribute(): string
    {
        return match($this->status) {
            'diterima'  => 'bg-green-100 text-green-700',
            'negosiasi' => 'bg-yellow-100 text-yellow-700',
            'ditolak'   => 'bg-red-100 text-red-700',
            'dikirim'   => 'bg-blue-100 text-blue-700',
            default     => 'bg-gray-100 text-gray-600',
        };
    }
}
