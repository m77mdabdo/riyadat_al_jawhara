<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Website-content options kept on the settings row as JSON: home page
 * sections, footer and floating elements. Null = built-in behaviour.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('home_sections')->nullable();
            $table->json('footer_options')->nullable();
            $table->json('floating_options')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['home_sections', 'footer_options', 'floating_options']);
        });
    }
};
