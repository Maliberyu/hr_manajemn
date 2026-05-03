<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lembur', function (Blueprint $table) {
            $table->string('keterangan', 255)->nullable()->after('alasan');
            $table->string('jenis', 2)->default('HB')->after('keterangan');
            $table->decimal('nominal', 12, 2)->nullable()->after('jenis');
            $table->text('catatan_atasan')->nullable()->after('approved_at');
            $table->unsignedBigInteger('approved_atasan_by')->nullable()->after('catatan_atasan');
            $table->timestamp('approved_atasan_at')->nullable()->after('approved_atasan_by');
            $table->text('catatan_hrd')->nullable()->after('approved_atasan_at');
            $table->unsignedBigInteger('approved_hrd_by')->nullable()->after('catatan_hrd');
            $table->timestamp('approved_hrd_at')->nullable()->after('approved_hrd_by');
        });

        // Update status column default ke nilai baru
        DB::statement("ALTER TABLE lembur MODIFY status VARCHAR(20) NOT NULL DEFAULT 'Menunggu Atasan'");
    }

    public function down(): void
    {
        Schema::table('lembur', function (Blueprint $table) {
            $table->dropColumn([
                'keterangan', 'jenis', 'nominal',
                'catatan_atasan', 'approved_atasan_by', 'approved_atasan_at',
                'catatan_hrd', 'approved_hrd_by', 'approved_hrd_at',
            ]);
        });
        DB::statement("ALTER TABLE lembur MODIFY status VARCHAR(20) NOT NULL DEFAULT 'menunggu'");
    }
};
