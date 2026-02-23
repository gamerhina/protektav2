<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->string('table_header_from', 14)->nullable()->default('#0f172a');
            $table->string('table_header_to', 14)->nullable()->default('#2563eb');
            $table->string('table_header_text_color', 14)->nullable()->default('#e2e8f0');
            $table->string('table_row_even_color', 14)->nullable()->default('#f7f8fb');
            $table->string('table_row_odd_color', 14)->nullable()->default('#fdfdfd');
            $table->string('table_row_text_color', 14)->nullable()->default('#0f172a');
            $table->string('table_border_color', 14)->nullable()->default('#e2e8f0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->dropColumn([
                'table_header_from',
                'table_header_to',
                'table_header_text_color',
                'table_row_even_color',
                'table_row_odd_color',
                'table_row_text_color',
                'table_border_color',
            ]);
        });
    }
};
