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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'nilai_percentage_p1', 'nilai_percentage_p2', 'nilai_percentage_pembahas'
            $table->json('value'); // store percentage values as JSON
            $table->string('type')->default('general'); // categorize settings
            $table->timestamps();
        });

        // Insert default percentage values
        DB::table('settings')->insert([
            [
                'key' => 'nilai_percentage_config',
                'value' => json_encode([
                    'p1' => 40,    // 40%
                    'p2' => 30,    // 30%
                    'pembahas' => 30  // 30%
                ]),
                'type' => 'nilai',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
