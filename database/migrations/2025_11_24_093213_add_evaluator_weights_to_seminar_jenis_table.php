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
            $table->decimal('p1_weight', 5, 2)->default(35.00)->after('keterangan');
            $table->decimal('p2_weight', 5, 2)->default(35.00)->after('p1_weight');
            $table->decimal('pembahas_weight', 5, 2)->default(30.00)->after('p2_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminar_jenis', function (Blueprint $table) {
            $table->dropColumn(['p1_weight', 'p2_weight', 'pembahas_weight']);
        });
    }
};
