<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->decimal('hero_overlay_opacity', 3, 2)->nullable()->default(0.90);
            $table->decimal('landing_background_opacity', 3, 2)->nullable()->default(0.95);
        });
    }

    public function down(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->dropColumn(['hero_overlay_opacity', 'landing_background_opacity']);
        });
    }
};
