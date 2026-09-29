<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only. Logo and default OG image are stored through Spatie
     * Media Library (media table), so they need no columns here.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('linkedin_url')->nullable()->after('snapchat_url');
            $table->string('x_url')->nullable()->after('linkedin_url');
            $table->string('youtube_url')->nullable()->after('x_url');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['linkedin_url', 'x_url', 'youtube_url']);
        });
    }
};
