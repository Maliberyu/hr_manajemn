<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_tarif_lembur_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->string('pendidikan', 20)->unique()->comment('Kode tingkat pendidikan (SMP, SMA, D3, S1, S2, dll)');
            $table->string('label', 50)->nullable()->comment('Label tampilan');
            $table->decimal('tarif_hb', 12, 2)->default(0)->comment('Tarif per jam hari biasa');
            $table->decimal('tarif_hr', 12, 2)->default(0)->comment('Tarif per jam hari raya/libur');
            $table->timestamps();
        });

        // Seed baris kosong untuk jenjang umum
        $defaults = [
            ['pendidikan' => 'SD',    'label' => 'SD / Sederajat'],
            ['pendidikan' => 'SMP',   'label' => 'SMP / Sederajat'],
            ['pendidikan' => 'SMA',   'label' => 'SMA / SMK / Sederajat'],
            ['pendidikan' => 'D1',    'label' => 'Diploma 1'],
            ['pendidikan' => 'D3',    'label' => 'Diploma 3'],
            ['pendidikan' => 'S1',    'label' => 'Sarjana (S1)'],
            ['pendidikan' => 'S2',    'label' => 'Magister (S2)'],
            ['pendidikan' => 'S3',    'label' => 'Doktor (S3)'],
        ];

        foreach ($defaults as $row) {
            DB::table('hr_tarif_lembur_pendidikan')->insert(array_merge($row, [
                'tarif_hb'   => 0,
                'tarif_hr'   => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_tarif_lembur_pendidikan');
    }
};
