<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Anonymous WhatsApp-button click events (no IP, no user agent, no
     * personal data): which page, which language, and what the visitor was
     * looking at. A lead is linked only when the context was known.
     */
    public function up(): void
    {
        Schema::create('whatsapp_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('page', 2048)->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('context_type', 20)->nullable();
            $table->unsignedBigInteger('context_id')->nullable();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index(['context_type', 'context_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_clicks');
    }
};
