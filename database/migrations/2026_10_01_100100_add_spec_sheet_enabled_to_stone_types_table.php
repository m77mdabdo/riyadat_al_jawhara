<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stone_types', function (Blueprint $table) {
            // Per-stone "Download spec sheet" (PDF); on for existing stones.
            $table->boolean('spec_sheet_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('stone_types', function (Blueprint $table) {
            $table->dropColumn('spec_sheet_enabled');
        });
    }
};
