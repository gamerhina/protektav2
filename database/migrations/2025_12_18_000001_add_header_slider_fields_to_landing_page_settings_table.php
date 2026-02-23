<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->json('landing_background_slides')->nullable()->after('landing_background_path');
            $table->boolean('landing_slider_enabled')->default(true)->after('landing_background_slides');
            $table->unsignedInteger('landing_slider_interval_ms')->default(6000)->after('landing_slider_enabled');
        });

        // Backfill: if existing single landing background exists, use it as the first slide.
        $rows = DB::table('landing_page_settings')
            ->select('id', 'landing_background_path', 'landing_background_slides')
            ->get();

        foreach ($rows as $row) {
            $hasSlides = !empty($row->landing_background_slides);
            if ($hasSlides || empty($row->landing_background_path)) {
                continue;
            }

            DB::table('landing_page_settings')
                ->where('id', $row->id)
                ->update([
                    'landing_background_slides' => json_encode([$row->landing_background_path]),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->dropColumn([
                'landing_background_slides',
                'landing_slider_enabled',
                'landing_slider_interval_ms',
            ]);
        });
    }
};
