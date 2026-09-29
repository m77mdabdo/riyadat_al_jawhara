<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only: every new column is nullable, so existing leads are untouched.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('stone_type_id')->constrained()->nullOnDelete();
            $table->string('locale', 5)->nullable()->after('source');
            $table->string('source_url', 2048)->nullable()->after('locale');
            $table->string('ip_address', 45)->nullable()->after('source_url');
            $table->string('user_agent', 512)->nullable()->after('ip_address');
            $table->timestamp('consented_at')->nullable()->after('user_agent');

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn(['locale', 'source_url', 'ip_address', 'user_agent', 'consented_at']);
        });
    }
};
