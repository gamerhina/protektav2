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
        Schema::table('dosen', function (Blueprint $table) {
            // Drop the unique constraint on nip
            $table->dropUnique(['nip']); // This assumes the constraint was named 'dosen_nip_unique'
            // Add a regular index instead
            $table->index('nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            // Drop the index
            $table->dropIndex(['nip']);
            // Recreate the unique constraint
            $table->unique('nip');
        });
    }
};
