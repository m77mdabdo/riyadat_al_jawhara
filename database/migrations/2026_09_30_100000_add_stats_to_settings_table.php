<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only: optional company statistics for the home-page counters.
     * Null = not shown (the site never displays invented numbers).
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedInteger('stat_projects_completed')->nullable();
            $table->unsignedSmallInteger('stat_years_experience')->nullable();
            $table->unsignedSmallInteger('stat_cities_served')->nullable();
            $table->unsignedInteger('stat_clients_served')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['stat_projects_completed', 'stat_years_experience', 'stat_cities_served', 'stat_clients_served']);
        });
    }
};
