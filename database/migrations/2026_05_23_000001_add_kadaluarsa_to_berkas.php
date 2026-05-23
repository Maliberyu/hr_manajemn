<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom kadaluarsa ke tabel berkas
        Schema::table('hr_berkas', function (Blueprint $table) {
            $table->date('tgl_kadaluarsa')->nullable()->after('keterangan');
            $table->boolean('notif_aktif')->default(false)->after('tgl_kadaluarsa');
        });

        // Tabel setting notifikasi kadaluarsa berkas
        Schema::create('hr_berkas_setting', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('hari_notif_1')->default(30); // warning awal (kuning)
            $table->unsignedInteger('hari_notif_2')->default(7);  // warning urgent (merah)
            $table->timestamps();
        });

        DB::table('hr_berkas_setting')->insert([
            'hari_notif_1' => 30,
            'hari_notif_2' => 7,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_berkas_setting');

        Schema::table('hr_berkas', function (Blueprint $table) {
            $table->dropColumn(['tgl_kadaluarsa', 'notif_aktif']);
        });
    }
};
