<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Pegawai extends Model
{
    // Tabel di SIK tidak punya created_at / updated_at
    public $timestamps = false;

    protected $table = 'pegawai';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nik',
        'nama',
        'jk',
        'jbtn',
        'jnj_jabatan',
        'kode_kelompok',
        'kode_resiko',
        'kode_emergency',
        'departemen',
        'bidang',
        'stts_wp',
        'stts_kerja',
        'npwp',
        'pendidikan',
        'gapok',
        'tmp_lahir',
        'tgl_lahir',
        'alamat',
        'kota',
        'mulai_kerja',
        'ms_kerja',
        'indexins',
        'bpd',
        'rekening',
        'stts_aktif',
        'wajibmasuk',
        'pengurang',
        'indek',
        'mulai_kontrak',
        'cuti_diambil',
        'dankes',
        'photo',
        'no_ktp',
    ];

    protected $casts = [
        'tgl_lahir'      => 'date',
        'mulai_kerja'    => 'date',
        'mulai_kontrak'  => 'date',
        'gapok'          => 'double',
        'pengurang'      => 'double',
        'dankes'         => 'double',
        'wajibmasuk'     => 'integer',
        'indek'          => 'integer',
        'cuti_diambil'   => 'integer',
    ];

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('stts_aktif', 'AKTIF');
    }

    public function scopeCari($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('nama', 'like', "%{$keyword}%")
              ->orWhere('nik', 'like', "%{$keyword}%")
              ->orWhere('jbtn', 'like', "%{$keyword}%");
        });
    }

    public function scopeDepartemen($query, string $dep)
    {
        return $query->where('departemen', $dep);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    /** URL foto profil (fallback ke avatar default jika kosong) */
    public function getFotoUrlAttribute(): string
    {
        if ($this->photo && Storage::disk('public')->exists($this->photo)) {
            return Storage::url($this->photo);
        }
        return asset('images/avatar-default.png');
    }

    /** Nama lengkap + jabatan */
    public function getNamaJabatanAttribute(): string
    {
        return "{$this->nama} – {$this->jbtn}";
    }

    /** Usia berdasarkan tgl_lahir */
    public function getUsiaAttribute(): int
    {
        return \Carbon\Carbon::parse($this->tgl_lahir)->age;
    }

    /** Masa kerja dalam tahun */
    public function getMasaKerjaAttribute(): string
    {
        $years = \Carbon\Carbon::parse($this->mulai_kerja)->diffInYears(now());
        $months = \Carbon\Carbon::parse($this->mulai_kerja)->diffInMonths(now()) % 12;
        return "{$years} tahun {$months} bulan";
    }

    // ─── Relasi ke tabel SIK yang sudah ada ────────────────────────────────────

    public function berkas(): HasMany
    {
        return $this->hasMany(BerkasPegawai::class, 'nik', 'nik')->with('jenis');
    }

    public function jadwalBulanan(): HasMany
    {
        return $this->hasMany(JadwalPegawai::class, 'id', 'id');
    }

    public function pengajuanCuti(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'nik', 'nik');
    }

    public function cutiDiajukan(): HasMany
    {
        // Sebagai penanggung jawab (nik_pj)
        return $this->hasMany(PengajuanCuti::class, 'nik_pj', 'nik');
    }

    public function evaluasiKinerja(): HasMany
    {
        return $this->hasMany(EvaluasiKinerjaPegawai::class, 'id', 'id');
    }

    public function pencapaianKinerja(): HasMany
    {
        return $this->hasMany(PencapaianKinerjaPegawai::class, 'id', 'id');
    }

    public function danKesehatan(): HasMany
    {
        return $this->hasMany(AmbilDankes::class, 'id', 'id');
    }

    public function angsuranKoperasi(): HasMany
    {
        return $this->hasMany(AngsuranKoperasi::class, 'id', 'id');
    }

    public function departemenRef(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'departemen', 'dep_id');
    }

    public function pendidikanRef(): BelongsTo
    {
        return $this->belongsTo(Pendidikan::class, 'pendidikan', 'tingkat');
    }

    // ─── Relasi ke tabel BARU ──────────────────────────────────────────────────

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'pegawai_id', 'id');
    }

    public function lembur(): HasMany
    {
        return $this->hasMany(Lembur::class, 'pegawai_id', 'id');
    }

    public function rekapAbsensi(): HasMany
    {
        return $this->hasMany(RekapAbsensi::class, 'pegawai_id', 'id');
    }

    public function trainingPeserta(): HasMany
    {
        return $this->hasMany(TrainingPeserta::class, 'pegawai_id', 'id');
    }

    public function sertifikasi(): HasMany
    {
        return $this->hasMany(Sertifikasi::class, 'pegawai_id', 'id');
    }

    public function userAkun(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class, 'pegawai_id', 'id');
    }
}