<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Master definisi shift ───────────────────────────────────────────
        Schema::create('hr_shift_master', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama', 100);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->boolean('melewati_tengah_malam')->default(false);
            $table->decimal('multiplier_lembur', 3, 1)->default(1.0);
            $table->boolean('aktif')->default(true);
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();
        });

        // ── 2. Setting global shift ────────────────────────────────────────────
        Schema::create('hr_shift_setting', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('toleransi_mismatch_menit')->default(30);
            $table->unsignedTinyInteger('max_tukar_shift_per_bulan')->default(3);
            $table->boolean('wajib_approval_double_shift')->default(true);
            $table->boolean('notif_mismatch_ke_atasan')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // ── 3. Pengajuan tukar shift ───────────────────────────────────────────
        Schema::create('hr_tukar_shift', function (Blueprint $table) {
            $table->id();
            $table->string('no_pengajuan', 30)->unique();
            $table->unsignedBigInteger('pemohon_id');
            $table->unsignedBigInteger('rekan_id');
            $table->date('tgl_shift_pemohon');
            $table->date('tgl_shift_rekan');
            $table->string('shift_pemohon_kode', 30);
            $table->string('shift_rekan_kode', 30);
            $table->text('alasan');
            $table->enum('status', [
                'menunggu_rekan',
                'menunggu_atasan',
                'disetujui',
                'ditolak_rekan',
                'ditolak_atasan',
            ])->default('menunggu_rekan');
            $table->text('catatan_rekan')->nullable();
            $table->unsignedBigInteger('approved_rekan_by')->nullable();
            $table->timestamp('approved_rekan_at')->nullable();
            $table->text('catatan_atasan')->nullable();
            $table->unsignedBigInteger('approved_atasan_by')->nullable();
            $table->timestamp('approved_atasan_at')->nullable();
            $table->unsignedBigInteger('dibuat_oleh');
            $table->timestamps();

            $table->index('pemohon_id');
            $table->index('rekan_id');
            $table->index('status');
        });

        // ── 4. Pengajuan double shift ──────────────────────────────────────────
        Schema::create('hr_double_shift', function (Blueprint $table) {
            $table->id();
            $table->string('no_pengajuan', 30)->unique();
            $table->unsignedBigInteger('pegawai_id');
            $table->date('tanggal');
            $table->string('shift_pertama_kode', 30);
            $table->string('shift_kedua_kode', 30);
            $table->text('alasan');
            $table->enum('status', [
                'menunggu_atasan',
                'disetujui',
                'ditolak',
            ])->default('menunggu_atasan');
            $table->text('catatan_atasan')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('lembur_id')->nullable();
            $table->unsignedBigInteger('dibuat_oleh');
            $table->timestamps();

            $table->index('pegawai_id');
            $table->index(['pegawai_id', 'tanggal']);
        });

        // ── 5. Jadwal realisasi ────────────────────────────────────────────────
        Schema::create('hr_jadwal_realisasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pegawai_id');
            $table->date('tanggal');
            $table->string('shift_kode', 30);
            $table->enum('sumber', [
                'absensi_auto',
                'tukar_shift',
                'double_shift',
                'manual',
            ])->default('absensi_auto');
            $table->unsignedBigInteger('tukar_shift_id')->nullable();
            $table->unsignedBigInteger('double_shift_id')->nullable();
            $table->string('catatan', 255)->nullable();
            $table->timestamps();

            $table->unique(['pegawai_id', 'tanggal']);
            $table->index('tanggal');
        });

        // ── Seed shift master ──────────────────────────────────────────────────
        DB::table('hr_shift_master')->insert([
            ['kode'=>'pagi',  'nama'=>'Shift Pagi',  'jam_mulai'=>'07:00:00','jam_selesai'=>'14:00:00','melewati_tengah_malam'=>0,'multiplier_lembur'=>1.0,'aktif'=>1,'urutan'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['kode'=>'sore',  'nama'=>'Shift Sore',  'jam_mulai'=>'14:00:00','jam_selesai'=>'21:00:00','melewati_tengah_malam'=>0,'multiplier_lembur'=>1.0,'aktif'=>1,'urutan'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['kode'=>'malam', 'nama'=>'Shift Malam', 'jam_mulai'=>'21:00:00','jam_selesai'=>'07:00:00','melewati_tengah_malam'=>1,'multiplier_lembur'=>1.5,'aktif'=>1,'urutan'=>3,'created_at'=>now(),'updated_at'=>now()],
            ['kode'=>'libur', 'nama'=>'Hari Libur',  'jam_mulai'=>'00:00:00','jam_selesai'=>'00:00:00','melewati_tengah_malam'=>0,'multiplier_lembur'=>2.0,'aktif'=>1,'urutan'=>4,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // ── Seed shift setting ─────────────────────────────────────────────────
        DB::table('hr_shift_setting')->insert([
            'toleransi_mismatch_menit'    => 30,
            'max_tukar_shift_per_bulan'   => 3,
            'wajib_approval_double_shift' => 1,
            'notif_mismatch_ke_atasan'    => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_jadwal_realisasi');
        Schema::dropIfExists('hr_double_shift');
        Schema::dropIfExists('hr_tukar_shift');
        Schema::dropIfExists('hr_shift_setting');
        Schema::dropIfExists('hr_shift_master');
    }
};
