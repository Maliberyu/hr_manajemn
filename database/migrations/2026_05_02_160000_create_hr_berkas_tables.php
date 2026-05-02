<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── JENIS BERKAS (master tipe dokumen, bebas ditambah HRD) ───────────
        Schema::create('hr_jenis_berkas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);        // "Ijazah Terakhir", "Pelatihan ACLS", dll
            $table->string('kategori', 80)->default('Umum'); // opsional grouping
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // ── BERKAS PEGAWAI (file upload per karyawan) ────────────────────────
        Schema::create('hr_berkas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_id')->constrained('hr_jenis_berkas')->cascadeOnDelete();
            $table->string('nik', 30)->index();
            $table->string('nama_file', 255);   // nama asli file
            $table->string('path', 500);        // path di storage
            $table->date('tgl_upload');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_berkas');
        Schema::dropIfExists('hr_jenis_berkas');
    }
};
