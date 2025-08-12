<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('builder_pages', function (Blueprint $table) {
            // Drop the existing unique constraint that includes theme
            $table->dropUnique(['key', 'theme']);

            // Drop the current theme column
            $table->dropColumn('theme');

            // Add the new theme_id foreign key column
            $table->foreignId('theme_id')->nullable()->constrained('builder_themes')->onDelete('set null');

            // Add new unique constraint with theme_id
            $table->unique(['key', 'theme_id']);
        });
    }

    public function down(): void
    {
        Schema::table('builder_pages', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique(['key', 'theme_id']);

            // Drop the foreign key constraint and column
            $table->dropForeign(['theme_id']);
            $table->dropColumn('theme_id');

            // Restore the original theme column
            $table->string('theme')->nullable();

            // Restore the original unique constraint
            $table->unique(['key', 'theme']);
        });
    }
};
