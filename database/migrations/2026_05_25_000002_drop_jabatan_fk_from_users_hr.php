<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus FK constraint jabatan_id (jabatan user tidak perlu merujuk pegawai.jbtn)
        DB::statement('ALTER TABLE users_hr DROP FOREIGN KEY jabatan_id');
        DB::statement('ALTER TABLE users_hr DROP INDEX jabatan_id');
    }

    public function down(): void
    {
        // Tidak di-restore karena constraint ini memang salah desain
    }
};
