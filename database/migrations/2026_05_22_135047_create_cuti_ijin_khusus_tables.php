<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ── Master Jenis Ijin Khusus (dinamis, admin dapat kelola) ───────────
        Schema::create('hr_jenis_ijin_khusus', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama', 100);
            $table->unsignedTinyInteger('max_hari')->nullable();  // null = tidak dibatasi
            $table->boolean('wajib_lampiran')->default(false);    // wajib upload dokumen
            $table->boolean('butuh_waktu')->default(false);       // perlu input jam (terlambat/pulang)
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();

            $table->index('aktif', 'hr_jenis_ijin_aktif_idx');
        });

        // Seed default jenis ijin khusus
        $now = now();
        DB::table('hr_jenis_ijin_khusus')->insert([
            ['kode'=>'NIKAH',      'nama'=>'Menikah',                    'max_hari'=>3, 'wajib_lampiran'=>false,'butuh_waktu'=>false,'urutan'=>1, 'aktif'=>true,'dibuat_oleh'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['kode'=>'NIKAH_ANAK', 'nama'=>'Menikahkan Anak',            'max_hari'=>2, 'wajib_lampiran'=>false,'butuh_waktu'=>false,'urutan'=>2, 'aktif'=>true,'dibuat_oleh'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['kode'=>'KHITAN',     'nama'=>'Khitanan / Baptis Anak',     'max_hari'=>2, 'wajib_lampiran'=>false,'butuh_waktu'=>false,'urutan'=>3, 'aktif'=>true,'dibuat_oleh'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['kode'=>'DUKA_IST',   'nama'=>'Duka Cita Suami/Istri/Anak', 'max_hari'=>3, 'wajib_lampiran'=>false,'butuh_waktu'=>false,'urutan'=>4, 'aktif'=>true,'dibuat_oleh'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['kode'=>'DUKA_OT',    'nama'=>'Duka Cita Orang Tua/Mertua', 'max_hari'=>2, 'wajib_lampiran'=>false,'butuh_waktu'=>false,'urutan'=>5, 'aktif'=>true,'dibuat_oleh'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['kode'=>'DUKA_SD',    'nama'=>'Duka Cita Saudara Kandung',  'max_hari'=>1, 'wajib_lampiran'=>false,'butuh_waktu'=>false,'urutan'=>6, 'aktif'=>true,'dibuat_oleh'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['kode'=>'IBADAH',     'nama'=>'Ibadah Keagamaan',           'max_hari'=>1, 'wajib_lampiran'=>true, 'butuh_waktu'=>false,'urutan'=>7, 'aktif'=>true,'dibuat_oleh'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['kode'=>'TERLAMBAT',  'nama'=>'Ijin Terlambat',             'max_hari'=>null,'wajib_lampiran'=>false,'butuh_waktu'=>true,'urutan'=>8, 'aktif'=>true,'dibuat_oleh'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['kode'=>'PULANG',     'nama'=>'Ijin Pulang Duluan',         'max_hari'=>null,'wajib_lampiran'=>false,'butuh_waktu'=>true,'urutan'=>9, 'aktif'=>true,'dibuat_oleh'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['kode'=>'LAINNYA',    'nama'=>'Lainnya',                    'max_hari'=>null,'wajib_lampiran'=>false,'butuh_waktu'=>false,'urutan'=>10,'aktif'=>true,'dibuat_oleh'=>null,'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Pengajuan Ijin Khusus ─────────────────────────────────────────────
        Schema::create('hr_pengajuan_ijin_khusus', function (Blueprint $table) {
            $table->id();
            $table->string('no_pengajuan', 25)->unique();
            $table->string('nik', 30)->index();
            $table->unsignedBigInteger('pegawai_id')->index();
            $table->unsignedBigInteger('jenis_ijin_id');
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->unsignedTinyInteger('durasi_hari')->nullable();
            $table->unsignedSmallInteger('durasi_menit')->nullable();
            $table->text('alasan');
            $table->string('file_lampiran', 255)->nullable();
            $table->string('status', 30)->default('Menunggu Atasan');
            $table->text('catatan_atasan')->nullable();
            $table->text('catatan_hrd')->nullable();
            $table->unsignedBigInteger('approved_atasan_by')->nullable();
            $table->timestamp('approved_atasan_at')->nullable();
            $table->unsignedBigInteger('approved_hrd_by')->nullable();
            $table->timestamp('approved_hrd_at')->nullable();
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();

            $table->foreign('jenis_ijin_id')->references('id')->on('hr_jenis_ijin_khusus');
            $table->index(['nik', 'status'],        'hr_ijk_nik_status_idx');
            $table->index(['tanggal_mulai'],         'hr_ijk_tgl_idx');
        });

        // ── Setting Cuti Tahunan (konfigurasi H-N) ────────────────────────────
        Schema::create('hr_cuti_setting', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('min_hari_pengajuan')->default(3); // H-3 configurable
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        DB::table('hr_cuti_setting')->insert(['min_hari_pengajuan' => 3, 'created_at' => $now, 'updated_at' => $now]);

        // ── Lock Cuti Global ─────────────────────────────────────────────────
        Schema::create('hr_cuti_lock', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_locked')->default(false);
            $table->text('alasan_kunci')->nullable();
            $table->unsignedBigInteger('dikunci_oleh')->nullable();
            $table->timestamp('dikunci_at')->nullable();
            $table->unsignedBigInteger('dibuka_oleh')->nullable();
            $table->timestamp('dibuka_at')->nullable();
            $table->timestamps();
        });
        DB::table('hr_cuti_lock')->insert(['is_locked' => false, 'created_at' => $now, 'updated_at' => $now]);

        // ── Request Buka Cuti (saat terkunci) ────────────────────────────────
        Schema::create('hr_cuti_unlock_request', function (Blueprint $table) {
            $table->id();
            $table->string('no_request', 25)->unique();
            $table->unsignedBigInteger('user_id');
            $table->date('tgl_rencana_mulai');
            $table->date('tgl_rencana_akhir');
            $table->text('alasan');
            $table->string('status', 20)->default('menunggu'); // menunggu | disetujui | ditolak
            $table->text('catatan_hrd')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('user_id', 'hr_unlock_user_idx');
            $table->index('status',  'hr_unlock_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_cuti_unlock_request');
        Schema::dropIfExists('hr_cuti_lock');
        Schema::dropIfExists('hr_cuti_setting');
        Schema::dropIfExists('hr_pengajuan_ijin_khusus');
        Schema::dropIfExists('hr_jenis_ijin_khusus');
    }
};
