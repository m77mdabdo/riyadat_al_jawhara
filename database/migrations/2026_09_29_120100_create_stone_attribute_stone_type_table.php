<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stone_attribute_stone_type', function (Blueprint $table) {
            $table->foreignId('stone_attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stone_type_id')->constrained()->cascadeOnDelete();

            $table->primary(['stone_attribute_id', 'stone_type_id']);
            $table->index('stone_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stone_attribute_stone_type');
    }
};
