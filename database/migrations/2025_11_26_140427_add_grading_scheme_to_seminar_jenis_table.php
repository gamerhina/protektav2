<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seminar_jenis', function (Blueprint $table) {
            // Add JSON field to store grading scheme
            // Default: 76+ = A, 71-75 = B+, 66-70 = B, 61-65 = C+, 56-60 = C, 50-55 = D, 0-49 = E
            $table->json('grading_scheme')->nullable()->after('pembahas_weight');
        });
        
        // Set default grading scheme for existing records
        DB::table('seminar_jenis')->update([
            'grading_scheme' => json_encode([
                ['min' => 76, 'max' => 100, 'grade' => 'A'],
                ['min' => 71, 'max' => 75.99, 'grade' => 'B+'],
                ['min' => 66, 'max' => 70.99, 'grade' => 'B'],
                ['min' => 61, 'max' => 65.99, 'grade' => 'C+'],
                ['min' => 56, 'max' => 60.99, 'grade' => 'C'],
                ['min' => 50, 'max' => 55.99, 'grade' => 'D'],
                ['min' => 0, 'max' => 49.99, 'grade' => 'E'],
            ])
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminar_jenis', function (Blueprint $table) {
            $table->dropColumn('grading_scheme');
        });
    }
};
