<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin overrides of the public interface texts (lang/{ar,en}/site.php).
 * A missing row or an empty value means "use the text shipped in lang/".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_texts', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191)->unique();
            $table->text('value_ar')->nullable();
            $table->text('value_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_texts');
    }
};
