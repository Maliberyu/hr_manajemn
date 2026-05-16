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
        // ── absensi (dari SIK, tanpa prefix hr_) ────────────────────────────
        Schema::table('absensi', function (Blueprint $table) {
            $table->index('pegawai_id',              'hr_absensi_pegawai_id_idx');
            $table->index('tanggal',                 'hr_absensi_tanggal_idx');
            $table->index('status',                  'hr_absensi_status_idx');
            $table->index('terlambat_menit',         'hr_absensi_terlambat_idx');
            $table->index(['pegawai_id', 'tanggal'], 'hr_absensi_pegawai_tanggal_idx');
        });

        // ── lembur (dari SIK, tanpa prefix hr_) ─────────────────────────────
        Schema::table('lembur', function (Blueprint $table) {
            $table->index('pegawai_id',              'hr_lembur_pegawai_id_idx');
            $table->index('tanggal',                 'hr_lembur_tanggal_idx');
            $table->index('status',                  'hr_lembur_status_idx');
            $table->index(['pegawai_id', 'status'],  'hr_lembur_pegawai_status_idx');
        });

        // ── rekap_absensi ─────────────────────────────────────────────────────
        Schema::table('rekap_absensi', function (Blueprint $table) {
            $table->index('pegawai_id',                   'hr_rekap_absensi_pegawai_idx');
            $table->index(['tahun', 'bulan'],             'hr_rekap_absensi_periode_idx');
            $table->unique(['pegawai_id', 'tahun', 'bulan'], 'hr_rekap_absensi_unique');
        });

        // ── hr_pengajuan_cuti ─────────────────────────────────────────────────
        Schema::table('hr_pengajuan_cuti', function (Blueprint $table) {
            $table->index('status',                             'hr_cuti_status_idx');
            $table->index('tanggal',                            'hr_cuti_tanggal_idx');
            $table->index('urgensi',                            'hr_cuti_urgensi_idx');
            $table->index(['nik', 'status'],                    'hr_cuti_nik_status_idx');
            $table->index(['tanggal_awal', 'tanggal_akhir'],    'hr_cuti_periode_idx');
        });

        // ── hr_pengajuan_ijin ─────────────────────────────────────────────────
        Schema::table('hr_pengajuan_ijin', function (Blueprint $table) {
            $table->index('pegawai_id', 'hr_ijin_pegawai_id_idx');
            $table->index('status',     'hr_ijin_status_idx');
            $table->index('tanggal',    'hr_ijin_tanggal_idx');
        });

        // ── hr_training_eksternal ─────────────────────────────────────────────
        Schema::table('hr_training_eksternal', function (Blueprint $table) {
            $table->index('pegawai_id',    'hr_training_ekst_pegawai_idx');
            $table->index('status',        'hr_training_ekst_status_idx');
            $table->index('tanggal_mulai', 'hr_training_ekst_tgl_mulai_idx');
        });

        // ── hr_iht_peserta ────────────────────────────────────────────────────
        Schema::table('hr_iht_peserta', function (Blueprint $table) {
            $table->index('status', 'hr_iht_peserta_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropIndex('hr_absensi_pegawai_id_idx');
            $table->dropIndex('hr_absensi_tanggal_idx');
            $table->dropIndex('hr_absensi_status_idx');
            $table->dropIndex('hr_absensi_terlambat_idx');
            $table->dropIndex('hr_absensi_pegawai_tanggal_idx');
        });

        Schema::table('lembur', function (Blueprint $table) {
            $table->dropIndex('hr_lembur_pegawai_id_idx');
            $table->dropIndex('hr_lembur_tanggal_idx');
            $table->dropIndex('hr_lembur_status_idx');
            $table->dropIndex('hr_lembur_pegawai_status_idx');
        });

        Schema::table('rekap_absensi', function (Blueprint $table) {
            $table->dropIndex('hr_rekap_absensi_pegawai_idx');
            $table->dropIndex('hr_rekap_absensi_periode_idx');
            $table->dropUnique('hr_rekap_absensi_unique');
        });

        Schema::table('hr_pengajuan_cuti', function (Blueprint $table) {
            $table->dropIndex('hr_cuti_status_idx');
            $table->dropIndex('hr_cuti_tanggal_idx');
            $table->dropIndex('hr_cuti_urgensi_idx');
            $table->dropIndex('hr_cuti_nik_status_idx');
            $table->dropIndex('hr_cuti_periode_idx');
        });

        Schema::table('hr_pengajuan_ijin', function (Blueprint $table) {
            $table->dropIndex('hr_ijin_pegawai_id_idx');
            $table->dropIndex('hr_ijin_status_idx');
            $table->dropIndex('hr_ijin_tanggal_idx');
        });

        Schema::table('hr_training_eksternal', function (Blueprint $table) {
            $table->dropIndex('hr_training_ekst_pegawai_idx');
            $table->dropIndex('hr_training_ekst_status_idx');
            $table->dropIndex('hr_training_ekst_tgl_mulai_idx');
        });

        Schema::table('hr_iht_peserta', function (Blueprint $table) {
            $table->dropIndex('hr_iht_peserta_status_idx');
        });
    }
};
