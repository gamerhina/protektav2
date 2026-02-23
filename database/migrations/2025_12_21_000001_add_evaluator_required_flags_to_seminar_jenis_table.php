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
        Schema::table('seminar_jenis', function (Blueprint $table) {
            $table->boolean('p1_required')->default(true)->after('pembahas_weight');
            $table->boolean('p2_required')->default(true)->after('p1_required');
            $table->boolean('pembahas_required')->default(true)->after('p2_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminar_jenis', function (Blueprint $table) {
            $table->dropColumn(['p1_required', 'p2_required', 'pembahas_required']);
        });
    }
};
