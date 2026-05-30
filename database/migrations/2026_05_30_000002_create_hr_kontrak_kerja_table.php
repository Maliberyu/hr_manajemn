<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hr_kontrak_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20)->index();
            $table->foreignId('jenis_kontrak_id')->constrained('hr_jenis_kontrak');
            $table->string('no_kontrak', 50)->nullable()->unique();
            $table->date('tgl_mulai');
            $table->date('tgl_selesai')->nullable(); // null = PKWTT
            $table->date('tgl_tanda_tangan')->nullable();
            $table->string('file_kontrak', 255)->nullable();
            $table->enum('status', ['aktif', 'berakhir', 'diperbarui', 'dibatalkan'])->default('aktif');
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users_hr')->nullOnDelete();
            $table->foreignId('diperbarui_oleh')->nullable()->constrained('users_hr')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_kontrak_kerja');
    }
};
