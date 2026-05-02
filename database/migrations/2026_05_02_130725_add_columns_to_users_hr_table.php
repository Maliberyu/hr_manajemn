<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users_hr', function (Blueprint $table) {
            if (!Schema::hasColumn('users_hr', 'nama')) {
                $table->string('nama')->after('id');
            }
            if (!Schema::hasColumn('users_hr', 'email')) {
                $table->string('email')->unique()->after('nama');
            }
            if (!Schema::hasColumn('users_hr', 'password')) {
                $table->string('password')->after('email');
            }
            if (!Schema::hasColumn('users_hr', 'email_verified')) {
                $table->boolean('email_verified')->default(false)->after('password');
            }
            if (!Schema::hasColumn('users_hr', 'google_id')) {
                $table->string('google_id')->nullable()->after('email_verified');
            }
            if (!Schema::hasColumn('users_hr', 'auth_provider')) {
                $table->string('auth_provider')->default('local')->after('google_id');
            }
            if (!Schema::hasColumn('users_hr', 'jabatan')) {
                $table->string('jabatan')->nullable()->after('auth_provider');
            }
            if (!Schema::hasColumn('users_hr', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('jabatan');
            }
            if (!Schema::hasColumn('users_hr', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('users_hr', 'last_login_ip')) {
                $table->string('last_login_ip')->nullable()->after('last_login_at');
            }
            if (!Schema::hasColumn('users_hr', 'remember_token')) {
                $table->rememberToken()->after('last_login_ip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users_hr', function (Blueprint $table) {
            $table->dropColumn([
                'nama', 'email', 'password', 'email_verified',
                'google_id', 'auth_provider', 'jabatan', 'status',
                'last_login_at', 'last_login_ip', 'remember_token',
            ]);
        });
    }
};
