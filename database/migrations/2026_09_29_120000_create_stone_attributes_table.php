<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-managed lookup values for stone specifications. `type` is one of
     * finish / colour / size / application (see App\Models\StoneAttribute).
     */
    public function up(): void
    {
        Schema::create('stone_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('slug');
            $table->string('hex_color', 7)->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'slug']);
            $table->index(['type', 'is_active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stone_attributes');
    }
};
