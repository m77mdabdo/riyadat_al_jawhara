<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_stone_type', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stone_type_id')->constrained()->cascadeOnDelete();

            $table->primary(['project_id', 'stone_type_id']);
            $table->index('stone_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_stone_type');
    }
};
