<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive, nullable, guarded. Themes without settings behave exactly as
     * before; consumers fall back to their own defaults when a key is absent.
     */
    public function up(): void
    {
        if (Schema::hasColumn('builder_themes', 'settings')) {
            return;
        }

        Schema::table('builder_themes', function (Blueprint $table) {
            $table->json('settings')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('builder_themes', 'settings')) {
            return;
        }

        Schema::table('builder_themes', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
    }
};
