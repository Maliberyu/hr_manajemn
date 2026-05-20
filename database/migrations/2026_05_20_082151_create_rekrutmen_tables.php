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
        // ── Permintaan SDM dari Atasan ────────────────────────────────────────
        Schema::create('hr_rekrutmen_request', function (Blueprint $table) {
            $table->id();
            $table->string('no_request', 25)->unique();
            $table->unsignedBigInteger('user_id');              // atasan yang mengajukan
            $table->string('posisi', 150);
            $table->string('departemen', 30)->nullable();
            $table->unsignedTinyInteger('jumlah')->default(1);
            $table->text('alasan');
            $table->date('tanggal_dibutuhkan')->nullable();
            $table->string('status', 30)->default('menunggu_hrd');
            // status: menunggu_hrd | menunggu_direktur | disetujui | ditolak
            $table->text('catatan_hrd')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('user_id',     'hr_req_user_idx');
            $table->index('status',      'hr_req_status_idx');
            $table->index('departemen',  'hr_req_dep_idx');
        });

        // ── Lowongan Kerja ────────────────────────────────────────────────────
        Schema::create('hr_lowongan', function (Blueprint $table) {
            $table->id();
            $table->string('no_lowongan', 25)->unique();
            $table->unsignedBigInteger('request_id')->nullable(); // dari request atau manual
            $table->string('posisi', 150);
            $table->string('departemen', 30)->nullable();
            $table->unsignedTinyInteger('kuota')->default(1);
            $table->date('tgl_buka')->nullable();
            $table->date('tgl_tutup')->nullable();
            $table->string('status', 20)->default('buka');
            // status: buka | proses_seleksi | tutup | dibatalkan
            $table->text('deskripsi')->nullable();
            $table->text('syarat')->nullable();
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();

            $table->index('status',     'hr_lowongan_status_idx');
            $table->index('departemen', 'hr_lowongan_dep_idx');
        });

        // ── Pelamar ───────────────────────────────────────────────────────────
        Schema::create('hr_pelamar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lowongan_id');
            $table->string('nama', 150);
            $table->string('email', 150)->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->unsignedTinyInteger('pengalaman_tahun')->nullable();
            $table->string('sumber', 30)->default('lainnya');
            // sumber: walk_in | referral | job_portal | media_sosial | lainnya
            $table->string('cv_path', 255)->nullable();
            $table->string('status', 30)->default('baru');
            // status: baru | seleksi_cv | interview | offering | diterima | ditolak
            $table->text('catatan')->nullable();
            $table->date('tanggal_apply');
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();

            $table->index('lowongan_id', 'hr_pelamar_lowongan_idx');
            $table->index('status',      'hr_pelamar_status_idx');
        });

        // ── Interview per Tahap ───────────────────────────────────────────────
        Schema::create('hr_interview', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelamar_id');
            $table->unsignedTinyInteger('tahap')->default(1);
            $table->string('label_tahap', 100)->default('HR Interview');
            $table->dateTime('jadwal');
            $table->string('metode', 20)->default('offline'); // online | offline
            $table->string('lokasi_atau_link', 255)->nullable();
            $table->unsignedBigInteger('pewawancara_id')->nullable();
            $table->string('status', 20)->default('dijadwalkan');
            // status: dijadwalkan | selesai | batal
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('pelamar_id', 'hr_interview_pelamar_idx');
            $table->index('status',     'hr_interview_status_idx');
        });

        // ── Penilaian Interview ───────────────────────────────────────────────
        Schema::create('hr_penilaian_interview', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('interview_id');
            $table->unsignedBigInteger('penilai_id');
            $table->decimal('nilai', 5, 2)->default(0); // 0-100
            $table->string('rekomendasi', 20)->nullable();
            // rekomendasi: lanjutkan | pertimbangkan | tolak
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['interview_id', 'penilai_id'], 'hr_penilaian_unique');
            $table->index('interview_id', 'hr_penilaian_interview_idx');
        });

        // ── Offering ──────────────────────────────────────────────────────────
        Schema::create('hr_offering', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelamar_id')->unique();
            $table->decimal('gaji_ditawarkan', 15, 2)->nullable();
            $table->string('status', 20)->default('draft');
            // status: draft | dikirim | diterima | negosiasi | ditolak
            $table->text('catatan')->nullable();
            $table->date('tanggal_offering')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('status', 'hr_offering_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_offering');
        Schema::dropIfExists('hr_penilaian_interview');
        Schema::dropIfExists('hr_interview');
        Schema::dropIfExists('hr_pelamar');
        Schema::dropIfExists('hr_lowongan');
        Schema::dropIfExists('hr_rekrutmen_request');
    }
};
