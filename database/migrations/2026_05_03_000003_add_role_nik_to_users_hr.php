<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users_hr', function (Blueprint $table) {
            $table->string('nik', 20)->nullable()->after('nama');
            $table->enum('role', ['karyawan', 'atasan', 'hrd', 'admin'])->default('karyawan')->after('jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('users_hr', function (Blueprint $table) {
            $table->dropColumn(['nik', 'role']);
        });
    }
};
