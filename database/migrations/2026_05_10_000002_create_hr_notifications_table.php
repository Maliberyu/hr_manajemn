<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');         // penerima notifikasi
            $table->string('type', 50);                    // cuti_submitted, approved, rejected, dll
            $table->string('title', 150);
            $table->string('message', 255);
            $table->string('link', 255)->nullable();       // URL tujuan saat diklik
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_notifications');
    }
};
