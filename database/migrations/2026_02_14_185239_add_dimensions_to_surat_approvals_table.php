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
        Schema::table('surat_approvals', function (Blueprint $table) {
            $table->integer('stamp_width')->default(120)->after('stamp_y');
            $table->integer('stamp_height')->default(120)->after('stamp_width');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_approvals', function (Blueprint $table) {
            $table->dropColumn(['stamp_width', 'stamp_height']);
        });
    }
};
