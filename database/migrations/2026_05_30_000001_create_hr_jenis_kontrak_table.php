<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hr_jenis_kontrak', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->tinyInteger('durasi_default_bulan')->nullable();
            $table->boolean('is_tetap')->default(false); // PKWTT = tidak ada tgl_selesai
            $table->string('keterangan', 200)->nullable();
            $table->timestamps();
        });

        DB::table('hr_jenis_kontrak')->insert([
            ['nama' => 'Probasi',  'durasi_default_bulan' => 3,    'is_tetap' => false, 'keterangan' => 'Masa percobaan 3 bulan',                    'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Magang',   'durasi_default_bulan' => 6,    'is_tetap' => false, 'keterangan' => 'Magang / PKL',                              'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'PKWT',     'durasi_default_bulan' => 12,   'is_tetap' => false, 'keterangan' => 'Perjanjian Kerja Waktu Tertentu',           'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'PKWTT',    'durasi_default_bulan' => null,  'is_tetap' => true,  'keterangan' => 'Perjanjian Kerja Waktu Tidak Tertentu (Tetap)', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_jenis_kontrak');
    }
};
