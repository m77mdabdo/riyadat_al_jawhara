<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Account switches for the admin: deactivate without deleting, ask for a new
 * password at next login, and the time of the last login.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->boolean('password_change_required')->default(false);
            $table->timestamp('last_login_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'password_change_required', 'last_login_at']);
        });
    }
};
