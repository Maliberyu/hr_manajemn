<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_tarif_lembur', function (Blueprint $table) {
            $table->id();
            $table->char('dep_id', 4)->unique();
            $table->decimal('tarif_hb', 12, 2)->default(0)->comment('Tarif per jam hari biasa');
            $table->decimal('tarif_hr', 12, 2)->default(0)->comment('Tarif per jam hari raya/libur');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_tarif_lembur');
    }
};
