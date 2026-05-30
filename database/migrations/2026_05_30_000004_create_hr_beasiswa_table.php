<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hr_beasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20);
            $table->enum('jenis', ['tugas_belajar','ijin_belajar','kursus','sertifikasi','lainnya']);
            $table->string('nama_program', 200);
            $table->string('institusi', 150);
            $table->string('kota', 100)->nullable();
            $table->decimal('biaya_diajukan', 15, 2)->default(0);
            $table->decimal('biaya_disetujui', 15, 2)->nullable();
            $table->date('tgl_mulai');
            $table->date('tgl_selesai')->nullable();
            $table->enum('status', [
                'menunggu_atasan','menunggu_hrd','disetujui','ditolak','selesai'
            ])->default('menunggu_atasan');
            $table->text('catatan_pengaju')->nullable();
            $table->string('catatan_atasan', 400)->nullable();
            $table->string('catatan_hrd', 400)->nullable();
            $table->string('file_proposal')->nullable();
            $table->string('file_hasil')->nullable();
            $table->unsignedBigInteger('diajukan_oleh');
            $table->unsignedBigInteger('approve_atasan_oleh')->nullable();
            $table->unsignedBigInteger('approve_hrd_oleh')->nullable();
            $table->timestamps();

            $table->foreign('nik')->references('nik')->on('pegawai')->onDelete('cascade');
            $table->foreign('diajukan_oleh')->references('id')->on('users_hr')->onDelete('cascade');
            $table->foreign('approve_atasan_oleh')->references('id')->on('users_hr')->onDelete('set null');
            $table->foreign('approve_hrd_oleh')->references('id')->on('users_hr')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_beasiswa');
    }
};
