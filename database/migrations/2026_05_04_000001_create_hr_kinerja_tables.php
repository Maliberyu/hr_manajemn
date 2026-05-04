<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══ PENILAIAN PRESTASI KERJA ══════════════════════════════════════════

        Schema::create('hr_kinerja_kriteria', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->decimal('bobot', 5, 2)->default(0)->comment('% bobot total = 100');
            $table->unsignedSmallInteger('urutan')->default(10);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('hr_kinerja_sub_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kriteria_id')->constrained('hr_kinerja_kriteria')->onDelete('cascade');
            $table->string('nama', 255);
            $table->unsignedSmallInteger('urutan')->default(10);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('hr_penilaian_prestasi', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20);
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedTinyInteger('semester');   // 1 atau 2
            $table->unsignedSmallInteger('tahun');
            $table->unsignedBigInteger('penilai_id'); // user id atasan
            $table->string('status', 15)->default('draft'); // draft / final
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('predikat', 20)->nullable();
            // Evaluasi akhir
            $table->text('kelebihan')->nullable();
            $table->text('kekurangan')->nullable();
            $table->text('saran')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['pegawai_id', 'semester', 'tahun'], 'uq_prestasi_periode');
        });

        Schema::create('hr_penilaian_prestasi_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('hr_penilaian_prestasi')->onDelete('cascade');
            $table->foreignId('kriteria_id')->constrained('hr_kinerja_kriteria');
            $table->unsignedTinyInteger('nilai')->default(3); // 1-5
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['penilaian_id', 'kriteria_id']);
        });

        // ══ PENILAIAN 360 DERAJAT ═════════════════════════════════════════════

        Schema::create('hr_kinerja_360_dimensi', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->decimal('bobot', 5, 2)->default(0)->comment('% bobot dimensi');
            $table->json('untuk_rater')->nullable()->comment('["atasan","rekan","bawahan","self"]');
            $table->unsignedSmallInteger('urutan')->default(10);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('hr_kinerja_360_aspek', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dimensi_id')->constrained('hr_kinerja_360_dimensi')->onDelete('cascade');
            $table->string('nama', 255);
            $table->unsignedSmallInteger('urutan')->default(10);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // Bobot per hubungan rater (global config)
        Schema::create('hr_kinerja_360_bobot_rater', function (Blueprint $table) {
            $table->string('hubungan', 20)->primary(); // atasan/rekan/bawahan/self
            $table->decimal('bobot', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('hr_penilaian_360', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20);
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedTinyInteger('semester');
            $table->unsignedSmallInteger('tahun');
            $table->string('status', 15)->default('setup'); // setup/aktif/selesai
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->date('deadline')->nullable();
            $table->unsignedBigInteger('dibuat_oleh');
            $table->timestamps();
            $table->unique(['pegawai_id', 'semester', 'tahun'], 'uq_360_periode');
        });

        Schema::create('hr_penilaian_360_rater', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('hr_penilaian_360')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable(); // user HR
            $table->string('hubungan', 20); // atasan/rekan/bawahan/self
            $table->string('nama_rater', 150)->nullable();
            $table->boolean('is_anonim')->default(true);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_penilaian_360_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rater_id')->constrained('hr_penilaian_360_rater')->onDelete('cascade');
            $table->foreignId('aspek_id')->constrained('hr_kinerja_360_aspek');
            $table->unsignedTinyInteger('nilai'); // 1-5
            $table->timestamps();
            $table->unique(['rater_id', 'aspek_id']);
        });

        Schema::create('hr_penilaian_360_komentar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('hr_penilaian_360')->onDelete('cascade');
            $table->foreignId('rater_id')->constrained('hr_penilaian_360_rater')->onDelete('cascade');
            $table->text('kekuatan')->nullable();
            $table->text('area_pengembangan')->nullable();
            $table->text('saran')->nullable();
            $table->timestamps();
        });

        // ══ SEED DATA DEFAULT ════════════════════════════════════════════════

        // 7 Kriteria Penilaian Prestasi
        $kriteria = [
            ['nama' => 'Disiplin',                  'bobot' => 20, 'urutan' => 10],
            ['nama' => 'Tanggung Jawab',             'bobot' => 20, 'urutan' => 20],
            ['nama' => 'Kejujuran',                  'bobot' => 15, 'urutan' => 30],
            ['nama' => 'Kerjasama',                  'bobot' => 15, 'urutan' => 40],
            ['nama' => 'Kepemimpinan',               'bobot' => 10, 'urutan' => 50],
            ['nama' => 'Sikap',                      'bobot' => 10, 'urutan' => 60],
            ['nama' => 'Inisiatif dan Kreatifitas',  'bobot' => 10, 'urutan' => 70],
        ];
        foreach ($kriteria as $k) {
            DB::table('hr_kinerja_kriteria')->insert([...$k, 'aktif' => 1, 'created_at' => now(), 'updated_at' => now()]);
        }

        // 4 Dimensi 360°
        $dimensi = [
            ['nama' => 'Kompetensi Pribadi',        'bobot' => 25, 'urutan' => 10, 'untuk_rater' => json_encode(['atasan','rekan','self'])],
            ['nama' => 'Kompetensi Interpersonal',  'bobot' => 25, 'urutan' => 20, 'untuk_rater' => json_encode(['atasan','rekan','bawahan','self'])],
            ['nama' => 'Kepemimpinan',              'bobot' => 20, 'urutan' => 30, 'untuk_rater' => json_encode(['atasan','bawahan'])],
            ['nama' => 'Kinerja Profesional',       'bobot' => 30, 'urutan' => 40, 'untuk_rater' => json_encode(['atasan','rekan','self'])],
        ];
        $dimensiIds = [];
        foreach ($dimensi as $d) {
            $id = DB::table('hr_kinerja_360_dimensi')->insertGetId([...$d, 'aktif' => 1, 'created_at' => now(), 'updated_at' => now()]);
            $dimensiIds[$d['nama']] = $id;
        }

        // Aspek per dimensi
        $aspek = [
            'Kompetensi Pribadi' => ['Integritas dan Kejujuran','Tanggung Jawab','Semangat Kerja','Disiplin','Kemandirian'],
            'Kompetensi Interpersonal' => ['Hubungan Kerja','Kerja Sama Tim','Keterbukaan Umpan Balik','Komunikasi Efektif','Tanpa Diskriminasi'],
            'Kepemimpinan' => ['Arahan dan Motivasi','Panutan Etika','Penyelesaian Konflik','Pengembangan Tim','Konsistensi Kebijakan'],
            'Kinerja Profesional' => ['Kompetensi Teknis','Ketepatan Waktu','Perbaikan Terus-menerus','Kepatuhan Prosedur RS','Kontribusi Tujuan Unit'],
        ];
        foreach ($aspek as $dimNama => $items) {
            foreach ($items as $i => $nama) {
                DB::table('hr_kinerja_360_aspek')->insert([
                    'dimensi_id' => $dimensiIds[$dimNama], 'nama' => $nama,
                    'urutan' => ($i + 1) * 10, 'aktif' => 1,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // Bobot rater default
        $bobotRater = [
            ['hubungan' => 'atasan',  'bobot' => 40],
            ['hubungan' => 'rekan',   'bobot' => 30],
            ['hubungan' => 'bawahan', 'bobot' => 20],
            ['hubungan' => 'self',    'bobot' => 10],
        ];
        foreach ($bobotRater as $b) {
            DB::table('hr_kinerja_360_bobot_rater')->insert([...$b, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_penilaian_360_komentar');
        Schema::dropIfExists('hr_penilaian_360_nilai');
        Schema::dropIfExists('hr_penilaian_360_rater');
        Schema::dropIfExists('hr_penilaian_360');
        Schema::dropIfExists('hr_kinerja_360_bobot_rater');
        Schema::dropIfExists('hr_kinerja_360_aspek');
        Schema::dropIfExists('hr_kinerja_360_dimensi');
        Schema::dropIfExists('hr_penilaian_prestasi_nilai');
        Schema::dropIfExists('hr_penilaian_prestasi');
        Schema::dropIfExists('hr_kinerja_sub_indikator');
        Schema::dropIfExists('hr_kinerja_kriteria');
    }
};
