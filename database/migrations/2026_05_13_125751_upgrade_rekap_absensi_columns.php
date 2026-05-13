<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename kolom lama → nama baru (prefiks total_)
        Schema::table('rekap_absensi', function (Blueprint $table) {
            $table->renameColumn('hadir',     'total_hadir');
            $table->renameColumn('sakit',     'total_sakit');
            $table->renameColumn('izin',      'total_izin');
            $table->renameColumn('alfa',      'total_alfa');
            $table->renameColumn('cuti',      'total_cuti');
            $table->renameColumn('terlambat', 'total_terlambat');
        });

        // Tambah kolom baru yang belum ada
        Schema::table('rekap_absensi', function (Blueprint $table) {
            $table->integer('total_menit_terlambat')->default(0)->after('total_terlambat');
            $table->float('total_lembur_jam', 8, 2)->default(0)->after('total_menit_terlambat');
            $table->unsignedTinyInteger('wajib_masuk')->default(25)->after('total_lembur_jam');
        });
    }

    public function down(): void
    {
        Schema::table('rekap_absensi', function (Blueprint $table) {
            $table->dropColumn(['total_menit_terlambat', 'total_lembur_jam', 'wajib_masuk']);
        });

        Schema::table('rekap_absensi', function (Blueprint $table) {
            $table->renameColumn('total_hadir',     'hadir');
            $table->renameColumn('total_sakit',     'sakit');
            $table->renameColumn('total_izin',      'izin');
            $table->renameColumn('total_alfa',      'alfa');
            $table->renameColumn('total_cuti',      'cuti');
            $table->renameColumn('total_terlambat', 'terlambat');
        });
    }
};
