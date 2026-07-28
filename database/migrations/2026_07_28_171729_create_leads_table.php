<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('stone_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('city')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['new', 'contacted', 'in_progress', 'closed'])->default('new');
            $table->enum('source', ['website', 'whatsapp'])->default('website');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
