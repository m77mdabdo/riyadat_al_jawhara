<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin replacements for the static site images (hero poster, textures,
 * heritage photos, banners). The image itself is a media-library file; a slot
 * without one keeps using public/images.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_images', function (Blueprint $table) {
            $table->id();
            $table->string('slot', 64)->unique();
            $table->string('alt_ar')->nullable();
            $table->string('alt_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_images');
    }
};
