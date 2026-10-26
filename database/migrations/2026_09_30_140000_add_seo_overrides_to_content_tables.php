<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only: optional per-page SEO overrides. Empty = use the normal
     * title/description.
     */
    private const TABLES = ['services', 'stone_types', 'projects', 'posts'];

    public function up(): void
    {
        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('meta_title_ar')->nullable();
                $table->string('meta_title_en')->nullable();
                $table->string('meta_description_ar', 500)->nullable();
                $table->string('meta_description_en', 500)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn(['meta_title_ar', 'meta_title_en', 'meta_description_ar', 'meta_description_en']);
            });
        }
    }
};
