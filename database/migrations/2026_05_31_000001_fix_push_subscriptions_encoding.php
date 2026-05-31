<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Update existing rows yang pakai encoding lama
        DB::table('push_subscriptions')
            ->where('content_encoding', 'aesgcm')
            ->update(['content_encoding' => 'aes128gcm']);

        // Ubah default kolom
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->string('content_encoding', 20)->default('aes128gcm')->change();
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->string('content_encoding', 20)->default('aesgcm')->change();
        });
    }
};
