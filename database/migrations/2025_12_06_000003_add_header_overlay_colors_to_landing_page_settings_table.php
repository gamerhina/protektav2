<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->string('header_overlay_from', 14)->nullable()->default('#0f172a');
            $table->string('header_overlay_to', 14)->nullable()->default('#172554');
        });
    }

    public function down(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->dropColumn(['header_overlay_from', 'header_overlay_to']);
        });
    }
};
