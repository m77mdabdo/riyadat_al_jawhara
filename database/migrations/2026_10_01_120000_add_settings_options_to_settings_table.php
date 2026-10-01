<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Options of the Settings sub-pages kept as JSON on the settings row
 * (contact map, SEO, analytics, notifications). Null = built-in behaviour /
 * the .env values.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('contact_options')->nullable();
            $table->json('seo_options')->nullable();
            $table->json('analytics_options')->nullable();
            $table->json('notification_options')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['contact_options', 'seo_options', 'analytics_options', 'notification_options']);
        });
    }
};
