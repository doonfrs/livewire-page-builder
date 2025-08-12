<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('builder_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('default_theme_id')
                ->nullable()
                ->constrained('builder_themes')
                ->nullOnDelete();
            $table->timestamps();
        });

        // Seed single settings row
        DB::table('builder_settings')->insert([
            'id' => 1,
            'default_theme_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('builder_settings');
    }
};
