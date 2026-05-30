<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hr_riwayat_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20);
            $table->enum('jenjang', ['SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3','Non-Formal']);
            $table->string('nama_institusi', 200);
            $table->string('jurusan', 100)->nullable();
            $table->smallInteger('tahun_masuk')->unsigned()->nullable();
            $table->smallInteger('tahun_lulus')->unsigned()->nullable();
            $table->decimal('ipk', 3, 2)->nullable();
            $table->string('file_ijazah')->nullable();
            $table->boolean('is_terakhir')->default(false);
            $table->string('keterangan', 300)->nullable();
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();

            $table->foreign('nik')->references('nik')->on('pegawai')->onDelete('cascade');
            $table->foreign('dibuat_oleh')->references('id')->on('users_hr')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_riwayat_pendidikan');
    }
};
