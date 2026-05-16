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
        Schema::create('hr_kpi_setting', function (Blueprint $table) {
            $table->id();
            // Bobot komponen (harus total = 100)
            $table->unsignedTinyInteger('bobot_kehadiran')->default(25);
            $table->unsignedTinyInteger('bobot_disiplin')->default(15);
            $table->unsignedTinyInteger('bobot_penilaian')->default(30);
            $table->unsignedTinyInteger('bobot_p360')->default(20);
            $table->unsignedTinyInteger('bobot_pelatihan')->default(10);
            // Target komponen
            $table->unsignedTinyInteger('target_hadir_pct')->default(95);   // % kehadiran minimum
            $table->unsignedSmallInteger('target_jam_pelatihan')->default(40); // jam/semester
            $table->unsignedTinyInteger('penalti_alfa')->default(5);          // poin kurang per hari alfa
            $table->unsignedTinyInteger('penalti_terlambat')->default(2);     // poin kurang per kejadian
            $table->timestamps();
        });

        // Insert default setting
        DB::table('hr_kpi_setting')->insert([
            'bobot_kehadiran'    => 25,
            'bobot_disiplin'     => 15,
            'bobot_penilaian'    => 30,
            'bobot_p360'         => 20,
            'bobot_pelatihan'    => 10,
            'target_hadir_pct'   => 95,
            'target_jam_pelatihan' => 40,
            'penalti_alfa'       => 5,
            'penalti_terlambat'  => 2,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_kpi_setting');
    }
};
