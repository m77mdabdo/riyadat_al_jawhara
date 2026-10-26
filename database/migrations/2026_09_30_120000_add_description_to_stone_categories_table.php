<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only: bilingual description for the category landing section.
     * The category image is stored through Media Library ("cover" collection).
     */
    public function up(): void
    {
        Schema::table('stone_categories', function (Blueprint $table) {
            $table->text('description_ar')->nullable()->after('name_en');
            $table->text('description_en')->nullable()->after('description_ar');
        });
    }

    public function down(): void
    {
        Schema::table('stone_categories', function (Blueprint $table) {
            $table->dropColumn(['description_ar', 'description_en']);
        });
    }
};
