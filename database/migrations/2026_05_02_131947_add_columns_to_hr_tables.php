<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── ABSENSI ────────────────────────────────────────────────────────────
        Schema::table('absensi', function (Blueprint $table) {
            if (!Schema::hasColumn('absensi', 'pegawai_id'))    $table->unsignedBigInteger('pegawai_id')->after('id');
            if (!Schema::hasColumn('absensi', 'tanggal'))       $table->date('tanggal')->after('pegawai_id');
            if (!Schema::hasColumn('absensi', 'jam_masuk'))     $table->time('jam_masuk')->nullable()->after('tanggal');
            if (!Schema::hasColumn('absensi', 'jam_keluar'))    $table->time('jam_keluar')->nullable()->after('jam_masuk');
            if (!Schema::hasColumn('absensi', 'status'))        $table->string('status', 20)->default('hadir')->after('jam_keluar'); // hadir|izin|sakit|alfa|cuti|libur
            if (!Schema::hasColumn('absensi', 'terlambat_menit')) $table->integer('terlambat_menit')->default(0)->after('status');
            if (!Schema::hasColumn('absensi', 'metode'))        $table->string('metode', 20)->default('manual')->after('terlambat_menit');
            if (!Schema::hasColumn('absensi', 'keterangan'))    $table->text('keterangan')->nullable()->after('metode');
        });

        // ── REKAP ABSENSI ──────────────────────────────────────────────────────
        Schema::table('rekap_absensi', function (Blueprint $table) {
            if (!Schema::hasColumn('rekap_absensi', 'pegawai_id')) $table->unsignedBigInteger('pegawai_id')->after('id');
            if (!Schema::hasColumn('rekap_absensi', 'tahun'))      $table->year('tahun')->after('pegawai_id');
            if (!Schema::hasColumn('rekap_absensi', 'bulan'))      $table->tinyInteger('bulan')->after('tahun');
            if (!Schema::hasColumn('rekap_absensi', 'hadir'))      $table->integer('hadir')->default(0)->after('bulan');
            if (!Schema::hasColumn('rekap_absensi', 'terlambat'))  $table->integer('terlambat')->default(0)->after('hadir');
            if (!Schema::hasColumn('rekap_absensi', 'izin'))       $table->integer('izin')->default(0)->after('terlambat');
            if (!Schema::hasColumn('rekap_absensi', 'sakit'))      $table->integer('sakit')->default(0)->after('izin');
            if (!Schema::hasColumn('rekap_absensi', 'alfa'))       $table->integer('alfa')->default(0)->after('sakit');
            if (!Schema::hasColumn('rekap_absensi', 'cuti'))       $table->integer('cuti')->default(0)->after('alfa');
        });

        // ── LEMBUR ────────────────────────────────────────────────────────────
        Schema::table('lembur', function (Blueprint $table) {
            if (!Schema::hasColumn('lembur', 'pegawai_id'))   $table->unsignedBigInteger('pegawai_id')->after('id');
            if (!Schema::hasColumn('lembur', 'tanggal'))      $table->date('tanggal')->after('pegawai_id');
            if (!Schema::hasColumn('lembur', 'jam_mulai'))    $table->time('jam_mulai')->after('tanggal');
            if (!Schema::hasColumn('lembur', 'jam_selesai'))  $table->time('jam_selesai')->after('jam_mulai');
            if (!Schema::hasColumn('lembur', 'durasi_jam'))   $table->decimal('durasi_jam', 4, 2)->default(0)->after('jam_selesai');
            if (!Schema::hasColumn('lembur', 'alasan'))       $table->text('alasan')->nullable()->after('durasi_jam');
            if (!Schema::hasColumn('lembur', 'status'))       $table->string('status', 20)->default('menunggu')->after('alasan'); // menunggu|disetujui|ditolak
            if (!Schema::hasColumn('lembur', 'approved_by'))  $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            if (!Schema::hasColumn('lembur', 'approved_at'))  $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        // ── REKRUTMEN ─────────────────────────────────────────────────────────
        Schema::table('rekrutmen', function (Blueprint $table) {
            if (!Schema::hasColumn('rekrutmen', 'posisi'))        $table->string('posisi')->after('id');
            if (!Schema::hasColumn('rekrutmen', 'departemen'))    $table->string('departemen')->nullable()->after('posisi');
            if (!Schema::hasColumn('rekrutmen', 'kuota'))         $table->integer('kuota')->default(1)->after('departemen');
            if (!Schema::hasColumn('rekrutmen', 'tgl_buka'))      $table->date('tgl_buka')->nullable()->after('kuota');
            if (!Schema::hasColumn('rekrutmen', 'tgl_tutup'))     $table->date('tgl_tutup')->nullable()->after('tgl_buka');
            if (!Schema::hasColumn('rekrutmen', 'status'))        $table->string('status', 20)->default('buka')->after('tgl_tutup'); // buka|tutup|selesai
            if (!Schema::hasColumn('rekrutmen', 'keterangan'))    $table->text('keterangan')->nullable()->after('status');
        });

        // ── PELAMAR ───────────────────────────────────────────────────────────
        Schema::table('pelamar', function (Blueprint $table) {
            if (!Schema::hasColumn('pelamar', 'rekrutmen_id'))  $table->unsignedBigInteger('rekrutmen_id')->after('id');
            if (!Schema::hasColumn('pelamar', 'nama'))          $table->string('nama')->after('rekrutmen_id');
            if (!Schema::hasColumn('pelamar', 'email'))         $table->string('email')->nullable()->after('nama');
            if (!Schema::hasColumn('pelamar', 'no_hp'))         $table->string('no_hp', 20)->nullable()->after('email');
            if (!Schema::hasColumn('pelamar', 'cv_path'))       $table->string('cv_path')->nullable()->after('no_hp');
            if (!Schema::hasColumn('pelamar', 'status'))        $table->string('status', 30)->default('diterima')->after('cv_path'); // diterima|seleksi|wawancara|lulus|gagal
            if (!Schema::hasColumn('pelamar', 'catatan'))       $table->text('catatan')->nullable()->after('status');
        });

        // ── TRAINING ──────────────────────────────────────────────────────────
        Schema::table('training', function (Blueprint $table) {
            if (!Schema::hasColumn('training', 'nama'))         $table->string('nama')->after('id');
            if (!Schema::hasColumn('training', 'penyelenggara')) $table->string('penyelenggara')->nullable()->after('nama');
            if (!Schema::hasColumn('training', 'tgl_mulai'))    $table->date('tgl_mulai')->nullable()->after('penyelenggara');
            if (!Schema::hasColumn('training', 'tgl_selesai'))  $table->date('tgl_selesai')->nullable()->after('tgl_mulai');
            if (!Schema::hasColumn('training', 'lokasi'))       $table->string('lokasi')->nullable()->after('tgl_selesai');
            if (!Schema::hasColumn('training', 'status'))       $table->string('status', 20)->default('rencana')->after('lokasi'); // rencana|berjalan|selesai|batal
            if (!Schema::hasColumn('training', 'keterangan'))   $table->text('keterangan')->nullable()->after('status');
        });

        // ── TRAINING PESERTA ──────────────────────────────────────────────────
        Schema::table('training_peserta', function (Blueprint $table) {
            if (!Schema::hasColumn('training_peserta', 'training_id'))  $table->unsignedBigInteger('training_id')->after('id');
            if (!Schema::hasColumn('training_peserta', 'pegawai_id'))   $table->unsignedBigInteger('pegawai_id')->after('training_id');
            if (!Schema::hasColumn('training_peserta', 'status'))       $table->string('status', 20)->default('terdaftar')->after('pegawai_id');
            if (!Schema::hasColumn('training_peserta', 'nilai'))        $table->decimal('nilai', 5, 2)->nullable()->after('status');
            if (!Schema::hasColumn('training_peserta', 'keterangan'))   $table->text('keterangan')->nullable()->after('nilai');
        });

        // ── SERTIFIKASI ───────────────────────────────────────────────────────
        Schema::table('sertifikasi', function (Blueprint $table) {
            if (!Schema::hasColumn('sertifikasi', 'pegawai_id'))       $table->unsignedBigInteger('pegawai_id')->after('id');
            if (!Schema::hasColumn('sertifikasi', 'nama_sertifikat'))  $table->string('nama_sertifikat')->after('pegawai_id');
            if (!Schema::hasColumn('sertifikasi', 'lembaga_penerbit')) $table->string('lembaga_penerbit')->nullable()->after('nama_sertifikat');
            if (!Schema::hasColumn('sertifikasi', 'tgl_terbit'))       $table->date('tgl_terbit')->nullable()->after('lembaga_penerbit');
            if (!Schema::hasColumn('sertifikasi', 'tgl_kadaluarsa'))   $table->date('tgl_kadaluarsa')->nullable()->after('tgl_terbit');
            if (!Schema::hasColumn('sertifikasi', 'file_path'))        $table->string('file_path')->nullable()->after('tgl_kadaluarsa');
        });
    }

    public function down(): void
    {
        // Cukup drop kolom yang ditambah jika perlu rollback
    }
};
