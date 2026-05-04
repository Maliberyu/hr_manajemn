<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── UMK per tahun Kab. Tasikmalaya ────────────────────────────────────
        Schema::create('hr_umk', function (Blueprint $table) {
            $table->id();
            $table->year('tahun')->unique();
            $table->decimal('nominal', 12, 2);
            $table->string('keterangan', 100)->nullable();
            $table->timestamps();
        });

        // ── Master skala gaji: Golongan × (Pendidikan opsional) × UMK Tahun ──
        Schema::create('hr_master_gaji', function (Blueprint $table) {
            $table->id();
            $table->string('golongan', 100);
            $table->string('pendidikan', 20)->nullable()->comment('kode pendidikan SIK, null = semua');
            $table->year('umk_tahun');
            $table->decimal('gaji_pokok', 12, 2)->default(0);
            $table->decimal('tunjangan_jabatan', 12, 2)->default(0);
            $table->string('keterangan', 200)->nullable();
            $table->timestamps();
            $table->unique(['golongan', 'pendidikan', 'umk_tahun'], 'uq_master_gaji');
        });

        // ── Master komponen tunjangan / potongan (configurable) ───────────────
        Schema::create('hr_komponen_gaji', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('jenis', 10)->default('tambah')->comment('tambah / kurang');
            $table->string('tipe', 20)->default('tetap')->comment('tetap / persen_gapok / persen_umk');
            $table->decimal('nilai', 12, 2)->default(0);
            $table->unsignedSmallInteger('urutan')->default(50);
            $table->boolean('aktif')->default(true);
            $table->string('keterangan', 200)->nullable();
            $table->timestamps();
        });

        // ── Konfigurasi global payroll (key-value) ────────────────────────────
        Schema::create('hr_payroll_config', function (Blueprint $table) {
            $table->string('key', 60)->primary();
            $table->string('value', 100)->default('0');
            $table->string('label', 150);
            $table->string('group', 30)->default('umum');
            $table->timestamps();
        });

        // ── Setting payroll per pegawai (golongan + UMK tahun) ─────────────────
        Schema::create('hr_pegawai_payroll', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20)->unique();
            $table->string('golongan', 100)->nullable();
            $table->year('umk_tahun')->nullable();
            $table->string('catatan', 255)->nullable();
            $table->timestamps();
        });

        // ── Slip gaji (header) ─────────────────────────────────────────────────
        Schema::create('hr_slip_gaji', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20);
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->string('status', 10)->default('draft')->comment('draft / final');
            $table->decimal('gaji_pokok', 12, 2)->default(0);
            $table->decimal('total_tunjangan', 12, 2)->default(0);
            $table->decimal('total_potongan', 12, 2)->default(0);
            $table->decimal('gaji_bersih', 12, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->unique(['pegawai_id', 'bulan', 'tahun'], 'uq_slip_periode');
        });

        // ── Detail komponen slip ───────────────────────────────────────────────
        Schema::create('hr_slip_komponen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slip_id');
            $table->string('nama', 100);
            $table->string('jenis', 10)->default('tambah')->comment('tambah / kurang');
            $table->decimal('nilai', 12, 2)->default(0);
            $table->unsignedSmallInteger('urutan')->default(50);
            $table->string('sumber', 20)->default('auto')->comment('auto / manual / sik');
            $table->timestamps();

            $table->foreign('slip_id')->references('id')->on('hr_slip_gaji')->onDelete('cascade');
        });

        // ── Seed konfigurasi default ───────────────────────────────────────────
        DB::table('hr_payroll_config')->insert([
            ['key' => 'bpjs_kes_pekerja',    'value' => '1',     'label' => 'BPJS Kesehatan Pekerja (%)',    'group' => 'bpjs'],
            ['key' => 'bpjs_kes_perusahaan', 'value' => '4',     'label' => 'BPJS Kesehatan Perusahaan (%)', 'group' => 'bpjs'],
            ['key' => 'bpjs_jht_pekerja',    'value' => '2',     'label' => 'BPJS JHT Pekerja (%)',          'group' => 'bpjs'],
            ['key' => 'bpjs_jht_perusahaan', 'value' => '3.7',   'label' => 'BPJS JHT Perusahaan (%)',       'group' => 'bpjs'],
            ['key' => 'bpjs_jp_pekerja',     'value' => '1',     'label' => 'BPJS JP Pekerja (%)',           'group' => 'bpjs'],
            ['key' => 'bpjs_jp_perusahaan',  'value' => '2',     'label' => 'BPJS JP Perusahaan (%)',        'group' => 'bpjs'],
            ['key' => 'potongan_absensi_aktif','value' => '0',   'label' => 'Potongan Absensi Aktif',        'group' => 'absensi'],
            ['key' => 'tarif_potongan_absensi','value' => '0',   'label' => 'Tarif Potongan Per Hari (Rp)',  'group' => 'absensi'],
            ['key' => 'masa_kerja_aktif',    'value' => '0',     'label' => 'Tunjangan Masa Kerja Aktif',    'group' => 'masa_kerja'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_slip_komponen');
        Schema::dropIfExists('hr_slip_gaji');
        Schema::dropIfExists('hr_pegawai_payroll');
        Schema::dropIfExists('hr_payroll_config');
        Schema::dropIfExists('hr_komponen_gaji');
        Schema::dropIfExists('hr_master_gaji');
        Schema::dropIfExists('hr_umk');
    }
};
