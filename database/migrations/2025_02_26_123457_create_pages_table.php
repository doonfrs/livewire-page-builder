<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('builder_pages', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('theme')->nullable();
            $table->longText('components')->nullable();
            $table->unique(['key', 'theme']);
            $table->boolean('is_block')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('builder_pages');
    }
};
