<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->longText('privacy_policy_ar')->nullable();
            $table->longText('privacy_policy_en')->nullable();
            $table->longText('terms_ar')->nullable();
            $table->longText('terms_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['privacy_policy_ar', 'privacy_policy_en', 'terms_ar', 'terms_en']);
        });
    }
};
