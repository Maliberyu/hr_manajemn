<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Master Setting Lembur ───────────────────────────────────────────
        Schema::create('hr_lembur_setting', function (Blueprint $table) {
            $table->id();
            // Metode otomatis: keduanya = sistem pilih berdasarkan ketersediaan jadwal shift
            $table->string('metode', 20)->default('keduanya'); // shift|jam_aktual|keduanya
            // Minimum jam untuk MASING-MASING metode
            $table->decimal('min_jam_lembur', 4, 2)->default(3.00);  // jam_aktual
            $table->decimal('min_jam_shift',  4, 2)->default(0.50);  // shift (0.5 = 30 menit)
            // Batas maksimal
            $table->decimal('max_jam_harian',   4, 2)->default(4.00);
            $table->decimal('max_jam_mingguan', 4, 2)->default(18.00);
            // Formula upah: gapok_173 (Pegawai.gapok/173) atau tarif_dept (hr_tarif_lembur)
            $table->string('formula_upah_jam', 20)->default('gapok_173');
            // Wajib approval atasan sebelum lembur direalisasikan
            $table->boolean('wajib_approval')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // ── Seed default setting ───────────────────────────────────────────────
        DB::table('hr_lembur_setting')->insert([
            'metode'           => 'keduanya',
            'min_jam_lembur'   => 3.00,
            'min_jam_shift'    => 0.50,
            'max_jam_harian'   => 4.00,
            'max_jam_mingguan' => 18.00,
            'formula_upah_jam' => 'gapok_173',
            'wajib_approval'   => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // ── 2. Tambah kolom ke tabel lembur ───────────────────────────────────
        Schema::table('lembur', function (Blueprint $table) {
            // Metode yang digunakan pada record ini
            $table->string('metode', 20)->nullable()->after('sumber_draft');
            // Data shift snapshot (untuk audit trail)
            $table->string('shift_kode', 30)->nullable()->after('metode');
            $table->time('jam_selesai_shift')->nullable()->after('shift_kode');
            // Multiplier yang digunakan (1.0/1.5/2.0)
            $table->decimal('multiplier', 3, 1)->nullable()->default(1.0)->after('jam_selesai_shift');
            // Upah per jam snapshot saat pengajuan
            $table->decimal('upah_per_jam', 12, 2)->nullable()->after('multiplier');
            // Durasi sebelum dicap (untuk audit)
            $table->decimal('durasi_aktual', 4, 2)->nullable()->after('upah_per_jam');
            // Catatan sistem (misal: "dipotong 5j→4j maks harian")
            $table->string('catatan_sistem', 255)->nullable()->after('durasi_aktual');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_lembur_setting');
        Schema::table('lembur', function (Blueprint $table) {
            $table->dropColumn([
                'metode', 'shift_kode', 'jam_selesai_shift',
                'multiplier', 'upah_per_jam', 'durasi_aktual', 'catatan_sistem',
            ]);
        });
    }
};
