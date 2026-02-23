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
            if (!Schema::hasColumn('assessment_aspects', 'persentase')) {
                $table->decimal('persentase', 5, 2)->default(0)->after('nama_aspek');
            }
            if (!Schema::hasColumn('assessment_aspects', 'type')) {
                $table->string('type')->default('input')->after('persentase'); // input, sum, prev_avg
            }
            if (!Schema::hasColumn('assessment_aspects', 'category')) {
                $table->string('category')->nullable()->after('type');
            }
            if (!Schema::hasColumn('assessment_aspects', 'related_aspects')) {
                $table->json('related_aspects')->nullable()->after('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_aspects', function (Blueprint $table) {
            //
        });
    }
};
