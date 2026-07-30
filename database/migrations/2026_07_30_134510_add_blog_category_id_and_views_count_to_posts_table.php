<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('blog_category_id')->nullable()->after('author_id')->constrained('blog_categories')->nullOnDelete();
            $table->unsignedInteger('views_count')->default(0)->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('blog_category_id');
            $table->dropColumn('views_count');
        });
    }
};
