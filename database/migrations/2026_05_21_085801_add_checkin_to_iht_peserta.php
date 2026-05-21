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
        Schema::table('hr_iht_peserta', function (Blueprint $table) {
            $table->dateTime('check_in_at')->nullable()->after('status');
            $table->dateTime('check_out_at')->nullable()->after('check_in_at');
        });
    }

    public function down(): void
    {
        Schema::table('hr_iht_peserta', function (Blueprint $table) {
            $table->dropColumn(['check_in_at', 'check_out_at']);
        });
    }
};
