<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->string('foto_masuk', 300)->nullable()->after('lng_masuk');
            $table->string('foto_keluar', 300)->nullable()->after('lng_keluar');
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn(['foto_masuk', 'foto_keluar']);
        });
    }
};
