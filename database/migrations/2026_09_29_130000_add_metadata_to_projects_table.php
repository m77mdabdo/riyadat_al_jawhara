<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only: nullable portfolio metadata. The existing stone_type_id
     * column is kept as the "primary stone"; extra stones go in the pivot.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('sector', 30)->nullable()->after('client_name');
            $table->decimal('area_m2', 10, 2)->nullable()->after('sector');
            $table->string('city_ar')->nullable()->after('location_en');
            $table->string('city_en')->nullable()->after('city_ar');

            $table->index('sector');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['sector']);
            $table->dropColumn(['sector', 'area_m2', 'city_ar', 'city_en']);
        });
    }
};
