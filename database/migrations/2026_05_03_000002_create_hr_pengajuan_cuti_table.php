<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hr_pengajuan_cuti', function (Blueprint $table) {
            $table->id();
            $table->string('no_pengajuan', 20)->unique();
            $table->date('tanggal');
            $table->string('nik', 20)->index();
            $table->string('urgensi', 50);
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->unsignedSmallInteger('jumlah');
            $table->string('alamat', 255);
            $table->string('kepentingan', 500);
            $table->string('nik_pj', 20)->nullable();

            // Level 1: Persetujuan Atasan Langsung
            $table->string('catatan_atasan', 500)->nullable();
            $table->timestamp('approved_atasan_at')->nullable();

            // Level 2: Persetujuan HRD
            $table->string('catatan_hrd', 500)->nullable();
            $table->timestamp('approved_hrd_at')->nullable();

            // Status keseluruhan
            $table->string('status', 30)->default('Menunggu Atasan');
            // Menunggu Atasan | Menunggu HRD | Disetujui | Ditolak Atasan | Ditolak HRD

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_pengajuan_cuti');
    }
};
