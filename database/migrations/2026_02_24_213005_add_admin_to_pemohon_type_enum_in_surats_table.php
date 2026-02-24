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
        // MySQL doesn't support modifying ENUM directly via Blueprint
        // We need to use raw SQL to alter the ENUM column to include 'admin'
        DB::statement("ALTER TABLE surats MODIFY COLUMN pemohon_type ENUM('mahasiswa', 'dosen', 'custom', 'admin') NOT NULL DEFAULT 'dosen'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values (without 'admin')
        // Note: This will fail if there are rows with pemohon_type = 'admin'
        DB::statement("ALTER TABLE surats MODIFY COLUMN pemohon_type ENUM('mahasiswa', 'dosen', 'custom') NOT NULL DEFAULT 'dosen'");
    }
};
