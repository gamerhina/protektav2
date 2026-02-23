<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE seminars MODIFY COLUMN status ENUM('diajukan','disetujui','ditolak','belum_lengkap','selesai') NOT NULL DEFAULT 'diajukan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE seminars MODIFY COLUMN status ENUM('diajukan','disetujui','ditolak','selesai') NOT NULL DEFAULT 'diajukan'");
    }
};
