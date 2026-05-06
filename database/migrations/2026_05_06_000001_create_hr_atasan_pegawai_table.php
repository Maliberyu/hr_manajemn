<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_atasan_pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20)->unique();        // FK ke pegawai.nik (SIK)
            $table->unsignedBigInteger('user_id');      // FK ke users_hr.id
            $table->string('keterangan', 100)->nullable(); // misal: "PJ IGD", "Kabid Keperawatan"
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users_hr')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_atasan_pegawai');
    }
};
