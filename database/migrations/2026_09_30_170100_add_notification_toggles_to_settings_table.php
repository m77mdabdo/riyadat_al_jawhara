<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Confirmation email to visitors who leave an email address.
            $table->boolean('lead_auto_reply')->default(true);
            // Email (in addition to the admin bell) for new comments awaiting review.
            $table->boolean('comment_mail_notifications')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['lead_auto_reply', 'comment_mail_notifications']);
        });
    }
};
