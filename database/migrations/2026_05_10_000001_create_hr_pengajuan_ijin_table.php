<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_pengajuan_ijin', function (Blueprint $table) {
            $table->id();
            $table->string('no_pengajuan', 30)->unique();
            $table->string('nik', 20);
            $table->unsignedBigInteger('pegawai_id')->nullable();
            $table->date('tanggal');
            $table->enum('jenis', ['sakit', 'terlambat', 'pulang_duluan']);
            $table->time('jam_mulai')->nullable();   // terlambat: jam masuk shift, pulang duluan: jam keluar rencana
            $table->time('jam_selesai')->nullable(); // terlambat: jam masuk sebenarnya, pulang duluan: jam keluar sebenarnya
            $table->unsignedInteger('durasi_menit')->nullable();
            $table->text('alasan');
            $table->string('file_surat')->nullable(); // wajib untuk sakit
            $table->enum('status', [
                'Menunggu Atasan',
                'Menunggu HRD',
                'Disetujui',
                'Ditolak Atasan',
                'Ditolak HRD',
            ])->default('Menunggu Atasan');
            $table->text('catatan_atasan')->nullable();
            $table->unsignedBigInteger('approved_atasan_by')->nullable();
            $table->datetime('approved_atasan_at')->nullable();
            $table->text('catatan_hrd')->nullable();
            $table->unsignedBigInteger('approved_hrd_by')->nullable();
            $table->datetime('approved_hrd_at')->nullable();
            $table->timestamps();

            $table->index(['nik', 'tanggal']);
            $table->index(['jenis', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_pengajuan_ijin');
    }
};
