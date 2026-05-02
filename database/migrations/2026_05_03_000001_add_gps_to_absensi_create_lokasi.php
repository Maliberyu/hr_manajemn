<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tambah kolom GPS ke tabel absensi ─────────────────────────────────
        Schema::table('absensi', function (Blueprint $table) {
            if (!Schema::hasColumn('absensi', 'lat_masuk'))
                $table->decimal('lat_masuk', 10, 7)->nullable()->after('metode');
            if (!Schema::hasColumn('absensi', 'lng_masuk'))
                $table->decimal('lng_masuk', 10, 7)->nullable()->after('lat_masuk');
            if (!Schema::hasColumn('absensi', 'lat_keluar'))
                $table->decimal('lat_keluar', 10, 7)->nullable()->after('lng_masuk');
            if (!Schema::hasColumn('absensi', 'lng_keluar'))
                $table->decimal('lng_keluar', 10, 7)->nullable()->after('lat_keluar');
            if (!Schema::hasColumn('absensi', 'lokasi_valid'))
                $table->boolean('lokasi_valid')->default(false)->after('lng_keluar');
            if (!Schema::hasColumn('absensi', 'diinput_oleh'))
                $table->unsignedBigInteger('diinput_oleh')->nullable()->after('lokasi_valid');
        });

        // ── Tabel lokasi absensi (titik koordinat + radius) ───────────────────
        if (!Schema::hasTable('hr_lokasi_absensi')) {
            Schema::create('hr_lokasi_absensi', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100);
                $table->string('alamat')->nullable();
                $table->decimal('lat', 10, 7);
                $table->decimal('lng', 10, 7);
                $table->integer('radius_meter')->default(100);
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_lokasi_absensi');
    }
};
