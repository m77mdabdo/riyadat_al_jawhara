<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-page content for the fixed public pages (about, contact, listing
 * intros, thank-you, 404, legal): extra content blocks, page options and SEO
 * (header texts are site texts, the banner a media file). Every empty column
 * falls back to the built-in behaviour.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('key', 32)->unique();
            $table->json('blocks')->nullable();
            $table->json('options')->nullable();
            $table->string('meta_title_ar')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->string('meta_description_ar', 500)->nullable();
            $table->string('meta_description_en', 500)->nullable();
            $table->boolean('noindex')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
