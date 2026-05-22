<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah kolom status dari ENUM ke VARCHAR agar fleksibel menambah nilai
        DB::statement("ALTER TABLE lembur MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'Menunggu Atasan'");

        // Tambah kolom sumber_draft untuk mencatat dari mana draft dibuat
        DB::statement("ALTER TABLE lembur ADD COLUMN sumber_draft VARCHAR(30) NULL AFTER status");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE lembur DROP COLUMN IF EXISTS sumber_draft");
        DB::statement("ALTER TABLE lembur MODIFY COLUMN status ENUM('Menunggu Atasan','Menunggu HRD','Disetujui','Ditolak Atasan','Ditolak HRD') NOT NULL DEFAULT 'Menunggu Atasan'");
    }
};
