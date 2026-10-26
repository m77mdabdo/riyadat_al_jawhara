<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only: nullable "local" / "imported" flag for filtering.
     */
    public function up(): void
    {
        Schema::table('stone_types', function (Blueprint $table) {
            $table->string('origin_type', 20)->nullable()->after('origin_en');
            $table->index('origin_type');
        });
    }

    public function down(): void
    {
        Schema::table('stone_types', function (Blueprint $table) {
            $table->dropIndex(['origin_type']);
            $table->dropColumn('origin_type');
        });
    }
};
