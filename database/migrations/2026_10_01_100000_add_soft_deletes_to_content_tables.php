<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trash / restore in the admin: nullable deleted_at on every table the admin
 * manages (existing rows keep NULL = not deleted).
 */
return new class extends Migration
{
    public const TABLES = [
        'services', 'stone_types', 'stone_categories', 'stone_attributes', 'projects', 'posts',
        'faqs', 'sliders', 'testimonials', 'team_members', 'blog_categories', 'tags', 'leads', 'comments',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropSoftDeletes();
            });
        }
    }
};
