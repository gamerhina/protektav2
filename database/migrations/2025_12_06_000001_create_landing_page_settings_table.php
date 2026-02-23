<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('landing_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->text('app_description')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_link')->nullable();
            $table->string('schedule_heading')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('login_background_path')->nullable();
            $table->string('landing_background_path')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('accent_color')->nullable();
            $table->string('button_color')->nullable();
            $table->timestamps();
        });

        DB::table('landing_page_settings')->insert([
            'id' => 1,
            'hero_title' => 'Pusat Informasi Seminar Protekta',
            'hero_subtitle' => 'Monitoring jadwal, status, serta ekosistem seminar dalam satu dashboard responsif.',
            'app_description' => 'Platform terpadu untuk mengelola seminar akademik, memantau peserta, dan menyajikan informasi aktual bagi seluruh civitas.',
            'cta_label' => 'Daftar Sekarang',
            'cta_link' => '/login',
            'schedule_heading' => 'Jadwal Seminar Terbaru',
            'primary_color' => '#1d4ed8',
            'secondary_color' => '#0f172a',
            'accent_color' => '#f97316',
            'button_color' => '#0ea5e9',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_page_settings');
    }
};
