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
        Schema::table('assessment_aspects', function (Blueprint $table) {
            $table->dropColumn('persentase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_aspects', function (Blueprint $table) {
            $table->decimal('persentase', 5, 2)->default(0)->after('nama_aspek');
        });
    }
};
