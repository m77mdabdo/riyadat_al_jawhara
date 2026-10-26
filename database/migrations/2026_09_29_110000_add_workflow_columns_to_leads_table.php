<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only: nullable assignee + follow-up date for the sales workflow.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->date('follow_up_at')->nullable()->after('assigned_to');

            $table->index('follow_up_at');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['follow_up_at']);
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn('follow_up_at');
        });
    }
};
