<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus stub lama yang kosong
        Schema::dropIfExists('training_peserta');
        Schema::dropIfExists('training');

        // ── IHT (In-House Training) ───────────────────────────────────────────
        Schema::create('hr_iht', function (Blueprint $table) {
            $table->id();
            $table->string('nama_training', 200);
            $table->string('penyelenggara', 100);
            $table->string('pemateri', 150)->nullable();
            $table->string('lokasi', 150);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->text('deskripsi')->nullable();
            $table->unsignedSmallInteger('kuota')->nullable();
            $table->enum('status', ['draft', 'aktif', 'selesai', 'dibatalkan'])->default('draft');
            $table->string('penandatangan_nama', 100)->nullable();
            $table->string('penandatangan_jabatan', 100)->nullable();
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();
        });

        // ── Peserta IHT ───────────────────────────────────────────────────────
        Schema::create('hr_iht_peserta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('iht_id');
            $table->unsignedBigInteger('pegawai_id');
            $table->enum('status', ['terdaftar', 'hadir', 'tidak_hadir', 'selesai'])->default('terdaftar');
            $table->decimal('nilai', 5, 2)->nullable();
            $table->string('nomor_sertifikat', 30)->nullable()->unique();
            $table->string('sertifikat_path', 300)->nullable();
            $table->datetime('sertifikat_at')->nullable();
            $table->timestamps();

            $table->unique(['iht_id', 'pegawai_id']);
            $table->foreign('iht_id')->references('id')->on('hr_iht')->onDelete('cascade');
        });

        // ── External Training ─────────────────────────────────────────────────
        Schema::create('hr_training_eksternal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->string('nama_training', 200);
            $table->string('lembaga', 150);
            $table->string('lokasi', 150)->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->decimal('biaya', 12, 2)->nullable()->default(0);
            $table->text('deskripsi')->nullable();
            $table->enum('mode', ['pengajuan', 'rekam_langsung'])->default('pengajuan');
            $table->enum('status', [
                'menunggu_atasan',
                'menunggu_hrd',
                'disetujui',
                'ditolak_atasan',
                'ditolak_hrd',
                'menunggu_validasi',
                'tervalidasi',
            ])->default('menunggu_atasan');
            // Approval atasan
            $table->unsignedBigInteger('atasan_id')->nullable();
            $table->text('catatan_atasan')->nullable();
            $table->unsignedBigInteger('approved_atasan_by')->nullable();
            $table->datetime('approved_atasan_at')->nullable();
            // Approval HRD
            $table->text('catatan_hrd')->nullable();
            $table->unsignedBigInteger('approved_hrd_by')->nullable();
            $table->datetime('approved_hrd_at')->nullable();
            // Sertifikat
            $table->string('nomor_sertifikat', 100)->nullable();
            $table->date('masa_berlaku')->nullable();
            $table->string('file_sertifikat', 300)->nullable();
            $table->datetime('uploaded_at')->nullable();
            // Validasi HR
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->datetime('validated_at')->nullable();
            $table->timestamps();
        });

        // ── Setting Training ──────────────────────────────────────────────────
        Schema::create('hr_training_setting', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('hr_training_setting')->insert([
            ['key' => 'logo_rs',   'value' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_training_setting');
        Schema::dropIfExists('hr_training_eksternal');
        Schema::dropIfExists('hr_iht_peserta');
        Schema::dropIfExists('hr_iht');
    }
};
